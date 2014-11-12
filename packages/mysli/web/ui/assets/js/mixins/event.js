mysli.web.ui.mixins.event = (function () {

    /// Trigger an event
    /// @param {string} event
    /// @param {array}  params
    function trigger(event, params) {

        if (typeof this.events[event] === 'undefined') {
            throw new Error("Invalid event id: `"+event+"`");
        }

        if (typeof params !== 'object') {
            params = [];
        }

        params.unshift(this);

        var call, id = null;
        for (id in this.events[event]) {
            if (!this.events[event].hasOwnProperty(id)) {
                continue;
            }
            call = this.events[event][id];
            if (typeof call === 'function') {
                call.apply(this, params);
            } else {
                throw new Error('Invalid type of callback: `'+id+'`');
            }
        }
    }
    /// Connect callback with an event
    /// @param   {string}   event [event*id]
    ///                     id can be assigned, to disconnect all events
    ///                     with that id, by calling: disconnect('*id')
    /// @param   {function} callback
    /// @returns {string}   id
    function connect(event, callback) {
        // Handle event with unique id
        var id = "";
        if (event.indexOf("*") > 0) {
            id = event.split("*", 2);
            id = "*" + id[1] + "*";
            event = id[0];
        }

        if (typeof this.events[event] === 'undefined') {
            throw new Error('No such event available: `'+event+'`');
        }

        this.events_counter++;
        id = id + event + '--' + this.events_counter;

        this.events[event][id] = callback;
        return id;
    }
    /// Disconnect particular event
    /// @param   {string} id full id, or specified unique id (eg *my_id)
    /// @returns {boolean}
    function disconnect(id) {
        var event = null;

        if (id.substr(0, 1) === '*') {
            id = id + "*";
            for (event in this.events) {
                for (var eid in this.events[event]) {
                    if (eid.substr(0, id.length) === id) {
                        delete(this.events[event][eid]);
                    }
                }
            }
            return true;
        }

        event = id.split('--', 2)[0];

        if (typeof this.events[event] !== 'undefined') {
            return delete(this.events[event][id]);
        } else {
            return false;
        }
    }

    return function () {
        this.events_counter = 0;
        this.events = {};

        this.trigger    = trigger;
        this.connect    = connect;
        this.disconnect = disconnect;

        return this;
    };

}());
