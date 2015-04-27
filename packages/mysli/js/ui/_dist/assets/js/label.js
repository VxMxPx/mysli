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
            var Label = (function (_super) {
                __extends(Label, _super);
                function Label(options) {
                    _super.call(this, options);
                    this.prop = js.common.mix({
                        // Label's text
                        text: '',
                        // Type, see constants defined above
                        type: Label.DEFAULT,
                        // Weather this label is connected to an input.
                        // Setting this to Widget, will force label's type to INPUT
                        input: null
                    }, this.prop);
                    this.type = this.prop.type;
                    this.text = this.prop.text;
                    if (this.prop.input) {
                        this.input = this.prop.input;
                    }
                }
                Object.defineProperty(Label, "DEFAULT", {
                    get: function () { return 1; },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Label, "TITLE", {
                    get: function () { return 2; },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Label, "INPUT", {
                    get: function () { return 3; },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Label.prototype, "type", {
                    /**
                     * Get/set type.
                     */
                    get: function () {
                        return this.prop.type;
                    },
                    set: function (type) {
                        var element;
                        switch (type) {
                            case Label.DEFAULT:
                                this.input = null;
                                element = $('<span />');
                                break;
                            case Label.TITLE:
                                this.input = null;
                                element = $('<h2 />');
                                break;
                            case Label.INPUT:
                                element = $('<label />');
                                break;
                            default:
                                throw new Error("Invalid type provided: " + type);
                        }
                        this.element.empty();
                        this.prop.type = type;
                        element.text(this.text);
                        this.element.append(element);
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Label.prototype, "text", {
                    /**
                     * Get/set text
                     */
                    get: function () {
                        return this.prop.text;
                    },
                    set: function (value) {
                        this.prop.text = value;
                        this.element.find(':first-child').text(value);
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Label.prototype, "input", {
                    /**
                     * Connect an input to a wiget.
                     */
                    get: function () {
                        return this.prop.input;
                    },
                    set: function (widget) {
                        if (!widget) {
                            if (this.input) {
                                this.element.find('label').prop('for', false);
                                this.prop.input = null;
                                this.prop.input.destroy();
                            }
                        }
                        else {
                            this.prop.input = widget;
                            if (!widget.element.prop('id')) {
                                widget.element.prop('id', widget.uid);
                            }
                            this.type = Label.INPUT;
                            this.element.find('label').prop('for', widget.uid);
                        }
                    },
                    enumerable: true,
                    configurable: true
                });
                Label.template = '<span class="ui-widget ui-title" />';
                return Label;
            })(ui.Widget);
            ui.Label = Label;
        })(ui = js.ui || (js.ui = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
