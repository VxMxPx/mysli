/// <reference path="widget.ts" />
/// <reference path="_inc.common.ts" />
var __extends = this.__extends || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    __.prototype = b.prototype;
    d.prototype = new __();
};
var mysli;
(function (mysli) {
    var js;
    (function (js) {
        var ui;
        (function (ui) {
            var Input = (function (_super) {
                __extends(Input, _super);
                function Input(options) {
                    if (options === void 0) { options = {}; }
                    _super.call(this, options);
                    this.element.addClass('ui-input');
                    this.$input = this.element;
                    this.prop.def({
                        type: Input.TYPE_TEXT,
                        placeholder: null,
                        label: null
                    });
                    this.prop.push(options, ['type!', 'label!', 'placeholder']);
                }
                Object.defineProperty(Input, "TYPE_TEXT", {
                    get: function () { return 'text'; },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Input, "TYPE_PASSWORD", {
                    get: function () { return 'password'; },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Input.prototype, "type", {
                    // Get/set type
                    get: function () {
                        return this.prop.type;
                    },
                    set: function (value) {
                        switch (value) {
                            case Input.TYPE_TEXT:
                                this.$input.prop('type', 'text');
                                break;
                            case Input.TYPE_PASSWORD:
                                this.$input.prop('type', 'password');
                                break;
                            default:
                                throw new Error("Invalid type: " + value);
                        }
                        this.prop.type = value;
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Input.prototype, "placeholder", {
                    // Get/set placeholder
                    get: function () {
                        return this.prop.placeholder;
                    },
                    set: function (value) {
                        this.prop.placeholder = value;
                        this.$input.prop('placeholder', value);
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Input.prototype, "label", {
                    // Get/set label
                    get: function () {
                        return this.prop.label;
                    },
                    set: function (value) {
                        if (this.prop.label) {
                            if (!value) {
                                this.$input.unwrap();
                                this.$element = this.$input;
                            }
                            else {
                                this.element.find('span').text(value);
                            }
                            this.prop.label = value;
                            return;
                        }
                        else {
                            if (value) {
                                this.$input.wrap('<label />');
                                this.$element = this.$input.parent();
                                this.element.prepend("<span>" + value + "</span>");
                                this.prop.label = value;
                            }
                        }
                    },
                    enumerable: true,
                    configurable: true
                });
                Input.template = '<input />';
                return Input;
            })(ui.Widget);
            ui.Input = Input;
        })(ui = js.ui || (js.ui = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
