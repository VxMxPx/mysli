/// <reference path="widget.ts" />
/// <reference path="box.ts" />
/// <reference path="generic_input.ts" />
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
            var Radio = (function (_super) {
                __extends(Radio, _super);
                function Radio(options) {
                    var _this = this;
                    if (options === void 0) { options = {}; }
                    _super.call(this, options);
                    this.element.addClass('ui-radio');
                    this.prop.def({
                        // Weather items is checked
                        checked: false,
                        // Weather item can be toggled
                        // (Switched on/off)
                        toggle: false
                    });
                    this.prop.push(options, ['toggle', 'checked']);
                    this.connect('click', function () {
                        if (!_this.disabled) {
                            if (!_this.prop.toggle && _this.checked) {
                                return;
                            }
                            _this.checked = !_this.checked;
                        }
                    });
                }
                Object.defineProperty(Radio.prototype, "checked", {
                    // Get/set checked value
                    get: function () {
                        return this.prop.checked;
                    },
                    set: function (value) {
                        this.prop.checked = value;
                        this.$input[value ? 'addClass' : 'removeClass']('checked');
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Radio.prototype, "toggle", {
                    // Get/set toggle state
                    get: function () {
                        return this.prop.toggle;
                    },
                    set: function (value) {
                        this.prop.toggle = value;
                    },
                    enumerable: true,
                    configurable: true
                });
                Radio.template = '<label><div class="ui-gi-input" /><span></span></label>';
                return Radio;
            })(ui.GenericInput);
            ui.Radio = Radio;
            var RadioGroup = (function (_super) {
                __extends(RadioGroup, _super);
                function RadioGroup(elements, options) {
                    var _this = this;
                    if (options === void 0) { options = {}; }
                    _super.call(this, options);
                    this.box = new ui.Box(options);
                    this.$element = this.box.element;
                    this.element.addClass('ui-radio-group');
                    var radio;
                    for (var i = 0; i < elements.length; i++) {
                        radio = new Radio(elements[i]);
                        if (radio.checked) {
                            if (this.checked) {
                                throw new Error("Cannot have two checked radios.");
                            }
                            else {
                                this.checked = radio;
                            }
                        }
                        radio.connect('click', function (e, self) {
                            if (_this.checked && (self.uid !== _this.checked.uid)) {
                                _this.checked.checked = false;
                            }
                            _this.checked = self;
                        });
                        this.box.push(radio);
                    }
                }
                return RadioGroup;
            })(ui.Widget);
            ui.RadioGroup = RadioGroup;
        })(ui = js.ui || (js.ui = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
