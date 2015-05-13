/// <reference path="box.ts" />
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
            var Titlebar = (function (_super) {
                __extends(Titlebar, _super);
                function Titlebar(options) {
                    if (options === void 0) { options = {}; }
                    options.orientation = ui.Box.VERTICAL;
                    _super.call(this, options);
                    this.element.addClass('ui-titlebar');
                }
                Titlebar.prototype.insert = function (widgets, at, options) {
                    if (widgets.constructor === Array) {
                        for (var i = 0; i < widgets.length; i++) {
                            widgets[i].flat = true;
                            _super.prototype.insert.call(this, widgets[i], at, options);
                        }
                        return widgets;
                    }
                    else {
                        widgets.flat = true;
                        return _super.prototype.insert.call(this, widgets, at, options);
                    }
                };
                return Titlebar;
            })(ui.Box);
            ui.Titlebar = Titlebar;
        })(ui = js.ui || (js.ui = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));