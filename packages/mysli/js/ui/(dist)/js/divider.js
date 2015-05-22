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
            var Divider = (function (_super) {
                __extends(Divider, _super);
                function Divider() {
                    _super.apply(this, arguments);
                }
                Divider.template = '<hr class="ui-divider" />';
                return Divider;
            })(ui.Widget);
            ui.Divider = Divider;
        })(ui = js.ui || (js.ui = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
