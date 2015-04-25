mysli.js.ui.extend.event = function (events) {

    'use strict';

    var dom_native = [
        // When widget is clicked
        // => ( object event, object this )
        'click',
        // When mouse cursor enter (parent) widget
        // => ( object event, object this )
        'mouse-enter',
        // When mouse cursor leave (parent) widget
        // => ( object event, object this )
        'mouse-leave',
        // When mouse cursor move over widget
        // => ( object event, object this )
        'mouse-move',
        // When mouse cursor move out of the widget (even to child)
        // => ( object event, object this )
        'mouse-out',
        // Mouse enter (even when enter to child element)
        // => ( object event, object this )
        'mouse-over',
        // Mouse up
        // => ( object event, object this )
        'mouse-up'
    ];

    var event = function (events, parent) {
        this.events = {};
        this.counter = 0;
        this.use_native = false;
        this.count_native = {};
        this.parent = parent;

        if (events[0] === '<native>') {
            this.use_native = true;
            events = events.slice(1);
            events = events.concat(dom_native);
        }

        for (var i = events.length - 1; i >= 0; i--) {
            this.events[events[i]] = {};
        }
    };

    event.prototype = {

        constructor : event,

        /**
         * Trigger an event.
         */
        trigger: function (event, params) {
            var call, id, _results;

            if (params === null) {
                params = [];
            }

            if (typeof this.events[event] === 'undefined') {
                throw new Error("Invalid event: "+event);
            }

            if (typeof params.push !== 'function') {
                throw new Error("Params needs to be an array!");
            }

            params.push(this);
            _results = [];

            for (id in this.events[event]) {
                if (!this.events[event].hasOwnProperty(id)) {
                    continue;
                }

                call = this.events[event][id];

                if (typeof call === 'function') {
                    _results.push(call.apply(this, params));
                } else {
                    throw new Error("Invalid type of callback: "+id);
                }
            }

            return _results;
        },

        /**
         * Connect callback with an event
         * @param   {string}   event [event*id]
         * id can be assigned, to disconnect all events
         * with that id, by calling: disconnect('*id')
         * @param   {function} callback
         * @returns {string}   id
         */
        connect: function(event, callback) {
            var id, _ref;

            _ref = this.extract_event_name(event);
            event = _ref[0];
            id = _ref[1];

            if (typeof this.events[event] === 'undefined') {
                throw new Error("No such event available: "+event);
            }

            this.counter++;
            id = "" + id + event + "--" + this.counter;
            this.events[event][id] = callback;

            // Handle native events
            if (this.use_native && typeof dom_native[event] !== 'undefined') {

                this.count_native[event] = typeof this.count_native[event] === 'undefined' ?
                    0 :
                    this.count_native[event]+1;

                // If more than one, this event was already set, and we need only one
                if (this.count_native[event] === 1) {
                    dom_event  = event.replace('-', '');
                    this.parent.$element.on(
                        dom_event,
                        this.trigger.bind(this, event));
                }
            }

            return id;
        },

        /**
         * Disconnect particular event
         * @param   {string} id full id, or specified unique id (eg *my_id)
         *          {array}  [event, id] to disconnect specific event
         * @returns {boolean}
         */
        disconnect: function(id) {
            var eid, event;

            if (typeof id !== 'object' && id.substr(0, 1) === '*') {
                id = id + "*";
                for (event in this.events) {
                    for (eid in this.events[event]) {
                        if (eid.substr(0, id.length) === id) {
                            this.disconnect_native(event);
                            delete this.events[event][eid];
                        }
                    }
                }
                return true;
            } else {
                if (typeof id !== 'object') {
                    event = id.split('--', 2)[0];
                } else {
                    event = id[0];
                    id = id[1];
                }

                if (typeof this.events[event] !== 'undefined') {
                    this.disconnect_native(event);
                    return delete this.events[event][id];
                } else {
                    return false;
                }
            }
        },

        /**
         * Process event*special_id and return an array
         * @param   {string} event
         * @returns {array}  [event, id]
         */
        extract_event_name: function(event) {
            var id;

            if (event.indexOf("*") > 0) {
                id = event.split("*", 2);
                event = id[0];
                id = "*" + id[1] + "*";
            } else {
                id = '';
            }
            return [event, id];
        },

        /**
         * Disconect native event.
         * @param {string} event
         */
        disconnect_native: function (event) {
            if (this.use_native && typeof dom_native[event] !== 'undefined') {

                this.count_native[event] = typeof this.count_native[event] === 'undefined' ?
                    0 :
                    this.count_native[event]-1;

                if (this.count_native[event] === 0) {
                    dom_event = event.replace('-', '');
                    this.parent.$element.off(dom_event);
                }
            }
        }
    };

    return new event(events, this);
};
