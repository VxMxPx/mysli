/// <reference path="_inc.common.ts" />
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
                    this.events_count_native = {};
                    this.events = {
                        // When widget is clicked
                        // => ( event: any, widget: Widget )
                        click: {},
                        // When widget is destroyed (destroy method called)
                        // => ( widget: Widget )
                        destroyed: {}
                    };
                    // Extends properties
                    this.prop = js.common.mix({
                        // Weather widget is disabled.
                        disabled: false,
                        // Widget's style
                        style: 'default',
                        // Weather widget (style) is flat
                        flat: false
                    }, options);
                    // Check for uid
                    if (typeof this.prop.uid === 'undefined') {
                        this.prop.uid = Widget.next_uid();
                    }
                    else {
                        if (Widget.uid_list.indexOf(this.prop.uid) > -1) {
                            throw new Error('Model with such ID is already added: ' + this.prop.uid);
                        }
                        else {
                            Widget.uid_list.push(this.prop.uid);
                        }
                    }
                    // Create element
                    this.$element = $(this['constructor']['template']);
                    this.$element.prop('id', this.prop.uid);
                    // Apply default properties
                    this.style = this.prop.style;
                    this.flat = this.prop.flat;
                    this.disabled = this.prop.disabled;
                }
                /**
                 * Generate a new UID and return it.
                 */
                Widget.next_uid = function () {
                    return 'mju-id-' + (++Widget.uid_count);
                };
                Object.defineProperty(Widget.prototype, "element", {
                    // Widget handling
                    /**
                     * Return a main element.
                     * @return {JQuery}
                     */
                    get: function () {
                        return this.$element;
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Widget.prototype, "uid", {
                    /**
                     * Return element's uid.
                     * @return {string}
                     */
                    get: function () {
                        return this.prop.uid;
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Widget.prototype, "disabled", {
                    // Get/set disabled status
                    get: function () {
                        return this.prop.disabled;
                    },
                    set: function (status) {
                        this.prop.disabled = status;
                        this.element.prop('disabled', status);
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Widget.prototype, "flat", {
                    // Get/set widget style to flat.
                    get: function () {
                        return this.prop.flat;
                    },
                    set: function (value) {
                        this.element[value ? 'addClass' : 'removeClass']('style-flat');
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Widget.prototype, "style", {
                    // Get/set widget's style (in general)
                    get: function () {
                        return this.prop.style;
                    },
                    set: function (style) {
                        if (this['constructor']['allowed_styles'].indexOf(style) > -1) {
                            this.element.removeClass("style-" + this.prop.style);
                            this.prop.style = style;
                            this.element.addClass("style-" + style);
                        }
                        else {
                            throw new Error("Invalid style: " + style + ", please use one of the following: " + this['constructor']['allowed_styles'].join(', '));
                        }
                    },
                    enumerable: true,
                    configurable: true
                });
                // Other
                /**
                 * Destroy this widget. This will trigger the 'destroyed' event.
                 */
                Widget.prototype.destroy = function () {
                    this.trigger('destroyed');
                    this.$element.remove();
                    Widget.uid_list.splice(Widget.uid_list.indexOf(this.uid), 1);
                    this.prop.uid = -1;
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
                    var _this = this;
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
                    if (Widget.events_native.indexOf(event) > -1) {
                        this.events_count_native[event] =
                            typeof this.events_count_native[event] === 'undefined' ?
                                1 :
                                this.events_count_native[event] + 1;
                        // Prevent registering event more than once
                        if (this.events_count_native[event] === 1) {
                            this.element.on(event.replace('-', ''), function (e) {
                                _this.trigger(event, e);
                            });
                        }
                    }
                    return id;
                };
                /**
                 * Trigger an event.
                 */
                Widget.prototype.trigger = function (event, params) {
                    if (params === void 0) { params = []; }
                    var call;
                    var _results = [];
                    if (typeof this.events[event] === 'undefined') {
                        throw new Error("Invalid event: " + event);
                    }
                    if (typeof params.push !== 'function') {
                        params = [params];
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
                 * @param id full id or specified id (eg *my_id) OR [event, id]
                 * @returns {boolean}
                 */
                Widget.prototype.disconnect = function (id) {
                    var event;
                    var eid;
                    if (typeof id === 'string' && id.substr(0, 1) === '*') {
                        id = id + "*";
                        for (event in this.events) {
                            if (!this.events.hasOwnProperty(event)) {
                                continue;
                            }
                            for (eid in this.events[event]) {
                                if (!this.events[event].hasOwnProperty(eid)) {
                                    continue;
                                }
                                if (eid.substr(0, id.length) === id) {
                                    this.event_disconnect_native(event);
                                    delete this.events[event][eid];
                                }
                            }
                        }
                        return true;
                    }
                    else {
                        if (typeof id === 'string') {
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
                 * Disconnect a native event.
                 * @param event
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
                 * @param event
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
                    // => ( event: any, widget: Widget )
                    'click',
                    // When mouse cursor enter (parent) widget
                    // => ( event: event, widget: Widget )
                    'mouse-enter',
                    // When mouse cursor leave (parent) widget
                    // => ( event: any, widget: Widget )
                    'mouse-leave',
                    // When mouse cursor move over widget
                    // => ( event: any, widget: Widget )
                    'mouse-move',
                    // When mouse cursor move out of the widget (even to child)
                    // => ( event: any, widget: Widget )
                    'mouse-out',
                    // Mouse enter (even when enter to child element)
                    // => ( event: any, widget: Widget )
                    'mouse-over',
                    // Mouse up
                    // => ( event: any, widget: Widget )
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
