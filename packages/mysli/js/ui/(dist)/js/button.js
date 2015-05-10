var __extends = this.__extends || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    __.prototype = b.prototype;
    d.prototype = new __();
};
/// <reference path="widget.ts" />
/// <reference path="_inc.common.ts" />
var mysli;
(function (mysli) {
    var js;
    (function (js) {
        var ui;
        (function (ui) {
            var Button = (function (_super) {
                __extends(Button, _super);
                function Button(options) {
                    if (options === void 0) { options = {}; }
                    _super.call(this, options);
                    this.prop.def({
                        // Buttons label (if any)
                        label: null,
                        // Weather button can be toggled
                        toggle: false,
                        // Weather button is pressed right now
                        pressed: false,
                        // Button's icon
                        icon: {
                            name: null,
                            position: 'left',
                            spin: false
                        }
                    });
                    this.prop.push(options, ['icon!', 'label!', 'toggle', 'pressed']);
                }
                Object.defineProperty(Button.prototype, "toggle", {
                    // Get/set toggle state
                    get: function () {
                        return this.prop.toggle;
                    },
                    set: function (value) {
                        var _this = this;
                        this.prop.toggle = value;
                        if (value) {
                            this.connect('click*self-toggle', function () {
                                _this.pressed = !_this.pressed;
                            });
                        }
                        else {
                            this.disconnect('click*self-toggle');
                        }
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Button.prototype, "pressed", {
                    // Get/set pressed state
                    get: function () {
                        return this.prop.pressed;
                    },
                    set: function (value) {
                        this.prop.pressed = value;
                        this.element[value ? 'addClass' : 'removeClass']('pressed');
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Button.prototype, "label", {
                    // Get/set button's label
                    get: function () {
                        return this.prop.label;
                    },
                    set: function (value) {
                        var $label = this.element.find('span.label');
                        var method;
                        this.prop.label = value;
                        if (!value) {
                            $label.remove();
                            return;
                        }
                        if (!$label.length) {
                            $label = $('<span class="label" />');
                            method = this.icon.position === 'right' ? 'prepend' : 'append';
                            this.element[method]($label);
                        }
                        $label.text(value);
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Button.prototype, "icon", {
                    // Get/set icon
                    get: function () {
                        return this.prop.icon;
                    },
                    set: function (options) {
                        var $icon;
                        var method;
                        var spin;
                        $icon = this.element.find('i.fa');
                        $icon.remove();
                        if (typeof options === 'string') {
                            options = { name: options };
                        }
                        if (!options['name']) {
                            this.prop.icon.name = null;
                            return;
                        }
                        this.prop.icon = js.common.mix(this.prop.icon, options);
                        method = this.prop.icon.position === 'right' ? 'append' : 'prepend';
                        spin = this.prop.icon.spin ? ' fa-spin' : '';
                        this.element[method]($("<i class=\"fa fa-" + this.prop.icon.name + spin + "\" />"));
                    },
                    enumerable: true,
                    configurable: true
                });
                Button.template = '<button class="ui-widget ui-button"></button>';
                Button.allowed_styles = ['default', 'alt', 'primary', 'confirm', 'attention'];
                return Button;
            })(ui.Widget);
            ui.Button = Button;
        })(ui = js.ui || (js.ui = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
