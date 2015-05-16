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
            var GenericInput = (function (_super) {
                __extends(GenericInput, _super);
                function GenericInput(options) {
                    if (options === void 0) { options = {}; }
                    _super.call(this, options);
                    this.element.addClass('ui-generic-input');
                    this.$input = this.element.find('.ui-gi-input');
                    this.$label = this.element.find('span');
                    this.prop.def({
                        label: null
                    });
                    this.prop.push(options, ['label!', 'disabled']);
                }
                Object.defineProperty(GenericInput.prototype, "disabled", {
                    // Override disabled status
                    get: function () {
                        return this.prop.disabled;
                    },
                    set: function (value) {
                        if (!this.$input)
                            return;
                        this.prop.disabled = value;
                        this.$input[value ? 'addClass' : 'removeClass']('disabled');
                        this.$input.prop('disabled', value);
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(GenericInput.prototype, "label", {
                    // Get/set label
                    get: function () {
                        return this.prop.label;
                    },
                    set: function (value) {
                        this.prop.label = value;
                        this.$label.text(value);
                    },
                    enumerable: true,
                    configurable: true
                });
                GenericInput.element = '<label><span></span><div class="ui-gi-input">Generic Input Should be Extended</div></label>';
                return GenericInput;
            })(ui.Widget);
            ui.GenericInput = GenericInput;
        })(ui = js.ui || (js.ui = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
