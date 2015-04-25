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
                        click: {},
                        destroyed: {}
                    };
                    this.prop = ui.Util.mix({
                        disabled: false,
                        style: 'default',
                        flat: false
                    }, options);
                    this.prop.allowed_styles = ['default', 'alt', 'primary', 'confirm', 'attention'];
                    if (typeof this.prop.uid !== 'undefined') {
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
                    this.$element = $(this.constructor.template);
                    this.$element.prop('id', this.prop.uid);
                    ui.Util.use(this.prop.this, {
                        style: 'style',
                        flat: 'flat',
                        disabled: 'disabled'
                    });
                }
                Widget.prototype.element = function () {
                    return this.$element;
                };
                Widget.prototype.uid = function () {
                    return this.prop.uid;
                };
                Widget.prototype.destroy = function () {
                    this.trigger('destroyed');
                    this.$element.remove();
                };
                Widget.prototype.disabled = function (status) {
                    if (status === void 0) { status = null; }
                    if (status !== null) {
                        this.prop.disabled = status;
                        this.$element.prop('disabled', status);
                    }
                    return this.prop.disabled;
                };
                Widget.prototype.flat = function (value) {
                    if (value === void 0) { value = null; }
                    if (value !== null && value !== this.prop.flat) {
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
                Widget.prototype.style = function (style) {
                    if (style === void 0) { style = null; }
                    var classes;
                    var current;
                    if (style !== null) {
                        if (this.prop.allowed_styles.indexOf(style) > -1) {
                            this.$element.removeClass(this.style());
                            this.$element.addClass("style-" + style);
                        }
                        else {
                            throw new Error("Invalid style: `" + style + "`, please use one of the following: " + this.prop.allowed_styles.join(', '));
                        }
                        return style;
                    }
                    else {
                        classes = this.$element[0].className.split(' ');
                        for (var i = classes.length - 1; i >= 0; i--) {
                            if (classes[i].substr(0, 6) === 'style-') {
                                current = classes[i].substr(6);
                                if (this.prop.allowed_styles.indexOf(current) > -1) {
                                    return current;
                                }
                            }
                        }
                    }
                };
                Widget.prototype.connect = function (event, callback) {
                    var _ref = Widget.event_extract_name(event);
                    var id;
                    event = _ref[0];
                    id = _ref[1];
                    if (typeof this.events[event] === 'undefined') {
                        throw new Error('No such event available: ' + event);
                    }
                    id = "" + id + event + "--" + (++this.events_count);
                    this.events[event][id] = callback;
                    if (typeof Widget.events_native[event] !== 'undefined') {
                        this.events_count_native[event] =
                            typeof this.events_count_native[event] === 'undefined' ?
                                0 :
                                this.events_count_native[event] + 1;
                        if (this.events_count_native[event] === 1) {
                            this.$element.on(event.replace('-', ''), this.trigger.bind(this, event));
                        }
                    }
                    return id;
                };
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
                Widget.events_native = [
                    'click',
                    'mouse-enter',
                    'mouse-leave',
                    'mouse-move',
                    'mouse-out',
                    'mouse-over',
                    'mouse-up'
                ];
                Widget.uid_count = 0;
                Widget.uid_list = [];
                Widget.template = '<div class="ui-widget" />';
                return Widget;
            })();
            ui.Widget = Widget;
        })(ui = js.ui || (js.ui = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
