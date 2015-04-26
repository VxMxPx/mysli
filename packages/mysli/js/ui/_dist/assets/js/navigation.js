var __extends = this.__extends || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    __.prototype = b.prototype;
    d.prototype = new __();
};
/// <reference path="widget.ts" />
/// <reference path="_inc.common.ts" />
var mysli;
(function (mysli) {
    var js;
    (function (js) {
        var ui;
        (function (ui) {
            var Navigation = (function (_super) {
                __extends(Navigation, _super);
                function Navigation(options) {
                    _super.call(this, options);
                    this.events = js.common.mix({
                        // When any navigation item is clicked
                        // ( string id, object this )
                        action: {}
                    }, this.events);
                    this.element.on('click', '.ui-navigation-item', function (e) {
                        var id;
                        e.stopPropagation();
                        id = e.currentTarget['id'].substr(6);
                        this.trigger('action', [id]);
                    });
                }
                Navigation.allowed_styles = ['default', 'alt'];
                Navigation.collection = new js.common.Arr();
                return Navigation;
            })(ui.Widget);
            ui.Navigation = Navigation;
        })(ui = js.ui || (js.ui = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
