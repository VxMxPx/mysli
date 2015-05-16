/// <reference path="widget.ts" />
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
            var Overlay = (function (_super) {
                __extends(Overlay, _super);
                function Overlay(options) {
                    if (options === void 0) { options = {}; }
                    _super.call(this, options);
                    this.prop.def({
                        busy: false,
                        visible: true
                    });
                    this.prop.push(options, ['busy', 'visible']);
                }
                Object.defineProperty(Overlay.prototype, "busy", {
                    // Get/set busy state.
                    get: function () {
                        return this.prop.busy;
                    },
                    set: function (status) {
                        this.prop.busy = status;
                        this.element[status ? 'addClass' : 'removeClass']('status-busy');
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Overlay.prototype, "visible", {
                    // Get/set visibility
                    get: function () {
                        return this.element.is(':visible');
                    },
                    set: function (status) {
                        this.prop.visible = status;
                        this.element[status ? 'show' : 'hide']();
                    },
                    enumerable: true,
                    configurable: true
                });
                Overlay.template = '<div class="ui-overlay ui-widget"><div class="ui-overlay-busy"><i class="fa fa-cog fa-spin"></i></div></div>';
                return Overlay;
            })(ui.Widget);
            ui.Overlay = Overlay;
        })(ui = js.ui || (js.ui = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
