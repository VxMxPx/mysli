var __extends = this.__extends || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    __.prototype = b.prototype;
    d.prototype = new __();
};
/// <reference path="widget.ts" />
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
                    // Properties
                    this.prop = ui.Util.mix({
                        label: null,
                        icon: {
                            name: null,
                            position: 'left',
                            spin: false
                        }
                    }, this.prop);
                    // Apply defaults
                    ui.Util.use(this.prop, this, {
                        label: 'label',
                        icon: 'icon',
                        style: 'style',
                        flat: 'flat',
                        disabled: 'disabled'
                    });
                }
                /**
                  * Get/Set button's label.
                  * @param  {string} value
                  * @return {string}
                  */
                Button.prototype.label = function (value) {
                    var $label = this.element().find('span.label');
                    var method;
                    if (typeof value !== 'undefined') {
                        if (value === null) {
                            $label.remove();
                            return '';
                        }
                        if (!$label.length) {
                            $label = $('<span class="label" />');
                            method = this.prop.icon.position === 'right' ? 'prepend' : 'append';
                            this.element()[method]($label);
                        }
                        $label.text(value);
                    }
                    return $label.text();
                };
                /**
                 * Get/Set Button's Icon
                 * @param  {any} String: icon || Object: {icon: 0, position: 0, spin: 0} || false: remove icon
                 * @return {any} {icon: 0, position: 0, spin: 0}
                 */
                Button.prototype.icon = function (icon) {
                    var $icon;
                    var method;
                    var spin;
                    if (typeof icon !== 'undefined') {
                        $icon = this.element().find('i.fa');
                        $icon.remove();
                        if (icon === null || icon.name === null) {
                            this.prop.icon.name = null;
                            return this.prop.icon;
                        }
                        if (typeof icon === 'string') {
                            this.prop.icon.name = icon;
                        }
                        else {
                            this.prop.icon = ui.Util.mix(this.prop.icon, icon);
                        }
                        method = this.prop.icon.position === 'right' ? 'append' : 'prepend';
                        spin = this.prop.icon.spin ? ' fa-spin' : '';
                        this.element()[method]($('<i class="fa fa-' + this.prop.icon.name + spin + '" />'));
                    }
                    return this.prop.icon;
                };
                Button.template = '<button class="ui-widget ui-button" />';
                return Button;
            })(ui.Widget);
            ui.Button = Button;
        })(ui = js.ui || (js.ui = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
