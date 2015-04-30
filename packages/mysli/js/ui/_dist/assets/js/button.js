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
                        label: null,
                        icon: {
                            name: null,
                            position: 'left',
                            spin: false
                        }
                    });
                    this.prop.push(options, ['icon!', 'label!']);
                }
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
