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
            var Checkbox = (function (_super) {
                __extends(Checkbox, _super);
                function Checkbox(options) {
                    var _this = this;
                    if (options === void 0) { options = {}; }
                    _super.call(this, options);
                    this.$checked = this.$input.find('i');
                    this.element.addClass('ui-checkbox');
                    this.prop.def({
                        checked: false
                    });
                    this.prop.push(options, ['checked']);
                    this.connect('click', function () {
                        if (!_this.disabled) {
                            _this.checked = !_this.checked;
                        }
                    });
                }
                Object.defineProperty(Checkbox.prototype, "checked", {
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
                Checkbox.template = '<label><div class="ui-gi-input"><i class="fa fa-check" /></div><span></span></label>';
                return Checkbox;
            })(ui.GenericInput);
            ui.Checkbox = Checkbox;
        })(ui = js.ui || (js.ui = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
