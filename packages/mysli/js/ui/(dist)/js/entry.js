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
            var Entry = (function (_super) {
                __extends(Entry, _super);
                function Entry(options) {
                    if (options === void 0) { options = {}; }
                    _super.call(this, options);
                    this.element.addClass('ui-entry');
                    this.prop.def({
                        type: Entry.TYPE_TEXT,
                        placeholder: null
                    });
                    this.prop.push(options, ['type!', 'placeholder']);
                }
                Object.defineProperty(Entry, "TYPE_TEXT", {
                    get: function () { return 'text'; },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Entry, "TYPE_PASSWORD", {
                    get: function () { return 'password'; },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Entry.prototype, "type", {
                    // Get/set type
                    get: function () {
                        return this.prop.type;
                    },
                    set: function (value) {
                        switch (value) {
                            case Entry.TYPE_TEXT:
                                this.$input.prop('type', 'text');
                                break;
                            case Entry.TYPE_PASSWORD:
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
                Object.defineProperty(Entry.prototype, "placeholder", {
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
                Entry.template = '<label><span></span><input class="ui-gi-input" /></label>';
                return Entry;
            })(ui.GenericInput);
            ui.Entry = Entry;
        })(ui = js.ui || (js.ui = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
