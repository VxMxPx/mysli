/// <reference path="_util.ts" />
/// <reference path="_jquery.d.ts" />
var mysli;
(function (mysli) {
    var js;
    (function (js) {
        var ui;
        (function (ui) {
            var Widget = (function () {
                function Widget(options) {
                    if (options === void 0) { options = {}; }
                    this.events_count = 0;
                    this.events_count_native = [];
                    this.events = {
                        // When widget is clicked
                        // => ( object event, object this )
                        click: {},
                        // When widget is destroyed (destroy method called)
                        // => ( object this )
                        destroyed: {}
                    };
                    // Extends properties
                    this.prop = ui.Util.mix({
                        // Weather widget is disabled.
                        disabled: false,
                        // Widget's style
                        style: 'default',
                        // Weather widget (style) is flat
                        flat: false
                    }, options);
                    // Check for uid
                    if (typeof this.prop.uid === 'undefined') {
                        this.prop.uid = 'mju-id-' + (++Widget.uid_count);
                    }
                    else {
                        if (Widget.uid_list.indexOf(this.prop.uid) > -1) {
                            throw new Error('Moduel with such ID is already added: ' + this.prop.uid);
                        }
                        else {
                            Widget.uid_list.push(this.prop.uid);
                        }
                    }
                    // Create element
                    this.$element = $(this.constructor['template']);
                    this.$element.prop('id', this.prop.uid);
                    // Apply default properties
                    ui.Util.use(this.prop, this, {
                        style: 'style',
                        flat: 'flat',
                        disabled: 'disabled'
                    });
                }
                // Widget handling
                /**
                 * Return a main element.
                 * @return {JQuery}
                 */
                Widget.prototype.element = function () {
                    return this.$element;
                };
                /**
                 * Return element's uid.
                 * @return {string}
                 */
                Widget.prototype.uid = function () {
                    return this.prop.uid;
                };
                /**
                 * Destroy this widget. This will trigger the 'destroyed' event.
                 */
                Widget.prototype.destroy = function () {
                    this.trigger('destroyed');
                    this.$element.remove();
                };
                /**
                 * Get/get disabled status.
                 * @param  {boolean} status
                 * @return {boolean}
                 */
                Widget.prototype.disabled = function (status) {
                    if (typeof status !== 'undefined') {
                        this.prop.disabled = status;
                        this.$element.prop('disabled', status);
                    }
                    return this.prop.disabled;
                };
                /**
                 * Get/set widget's style to be flat
                 * @param  {boolean} value
                 * @return {boolean}
                 */
                Widget.prototype.flat = function (value) {
                    if (typeof value !== 'undefined' && value !== this.prop.flat) {
                        if (value) {
                            this.$element.addClass('style-flat');
                        }
                        else {
                            this.$element.removeClass('style-flat');
                        }
                        this.prop.flat = value;
                    }
                    return this.prop.flat;
                };
                /**
                 * Get/set widget's style.
                 * @param  {string} style
                 * @return {string}
                 */
                Widget.prototype.style = function (style) {
                    var classes;
                    var current;
                    if (typeof style !== 'undefined') {
                        if (this.constructor['allowed_styles'].indexOf(style) > -1) {
                            this.prop.style = style;
                            this.$element.removeClass(this.prop.style);
                            this.$element.addClass("style-" + style);
                        }
                        else {
                            throw new Error("Invalid style: `" + style + "`, please use one of the following: " + this.constructor['allowed_styles'].join(', '));
                        }
                    }
                    return this.prop.style;
                };
                // Events
                /**
                 * Connect callback with an event.
                 * @param  {string}   event    event*id (id can be assigned,
                 * to disconnect all events with that particular id,
                 * by calling: disconnect('*id'))
                 * @param  {Function} callback
                 * @return {string}
                 */
                Widget.prototype.connect = function (event, callback) {
                    var _ref = Widget.event_extract_name(event);
                    var id;
                    event = _ref[0];
                    id = _ref[1];
                    if (typeof this.events[event] === 'undefined') {
                        throw new Error('No such event available: ' + event);
                    }
                    // Create new ID
                    id = "" + id + event + "--" + (++this.events_count);
                    this.events[event][id] = callback;
                    // Handle native events
                    if (typeof Widget.events_native[event] !== 'undefined') {
                        this.events_count_native[event] =
                            typeof this.events_count_native[event] === 'undefined' ?
                                0 :
                                this.events_count_native[event] + 1;
                        // Prevent registering event more than once
                        if (this.events_count_native[event] === 1) {
                            this.$element.on(event.replace('-', ''), this.trigger.bind(this, event));
                        }
                    }
                    return id;
                };
                /**
                 * Trigger an event.
                 * @param  {string} event
                 * @param  {array}  params
                 * @return {array}
                 */
                Widget.prototype.trigger = function (event, params) {
                    if (params === void 0) { params = []; }
                    var call;
                    var _results = [];
                    if (typeof this.events[event] === 'undefined') {
                        throw new Error("Invalid event: " + event);
                    }
                    if (typeof params.push !== 'function') {
                        throw new Error("Params needs to be an array!");
                    }
                    params.push(this);
                    for (var id in this.events[event]) {
                        if (!this.events[event].hasOwnProperty(id)) {
                            continue;
                        }
                        call = this.events[event][id];
                        if (typeof call === 'function') {
                            _results.push(call.apply(this, params));
                        }
                        else {
                            throw new Error("Invalid type of callback: " + id);
                        }
                    }
                    return _results;
                };
                /**
                 * Disconnect particular event.
                 * @param  {any} id
                 *   string: full id, or specified id (eg *my_id)
                 *   array:  [event, id] to disconnect specific event
                 * @return {boolean}
                 */
                Widget.prototype.disconnect = function (id) {
                    var event;
                    var eid;
                    if (typeof id !== 'object' && id.substr(0, 1) === '*') {
                        id = id + "*";
                        for (event in this.events) {
                            for (eid in this.events[event]) {
                                if (eid.substr(0, id.length) === id) {
                                    this.event_disconnect_native(event);
                                    delete this.events[event][eid];
                                }
                            }
                        }
                        return true;
                    }
                    else {
                        if (typeof id !== 'object') {
                            event = id.split('--', 2)[0];
                        }
                        else {
                            event = id[0];
                            id = id[1];
                        }
                        if (typeof this.events[event] !== 'undefined') {
                            this.event_disconnect_native(event);
                            return delete this.events[event][id];
                        }
                        else {
                            return false;
                        }
                    }
                };
                /**
                 * Disconnect native event.
                 * @param {string} event
                 */
                Widget.prototype.event_disconnect_native = function (event) {
                    if (typeof Widget.events_native[event] !== 'undefined') {
                        this.events_count_native[event] =
                            typeof this.events_count_native[event] === 'undefined' ?
                                0 :
                                this.events_count_native[event] - 1;
                        if (this.events_count_native[event] === 0) {
                            this.$element.off(event.replace('-', ''));
                        }
                    }
                };
                /**
                 * Process event*special_id and return an array.
                 * @param  {string} event
                 * @return {array}        [event, id]
                 */
                Widget.event_extract_name = function (event) {
                    var id;
                    var idr = '';
                    if (event.indexOf("*") > 0) {
                        id = event.split("*", 2);
                        event = id[0];
                        idr = "*" + id[1] + "*";
                    }
                    return [event, idr];
                };
                // Events
                Widget.events_native = [
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
                // Widget's Unique ID
                Widget.uid_count = 0;
                Widget.uid_list = [];
                // Element's template & element
                Widget.template = '<div class="ui-widget" />';
                // Properties
                Widget.allowed_styles = ['default', 'alt', 'primary', 'confirm', 'attention'];
                return Widget;
            })();
            ui.Widget = Widget;
        })(ui = js.ui || (js.ui = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
