var __extends = this.__extends || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    __.prototype = b.prototype;
    d.prototype = new __();
};
/// <reference path="button.ts" />
/// <reference path="box.ts" />
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
                function Navigation(items, options) {
                    if (options === void 0) { options = {}; }
                    _super.call(this, options);
                    this.container = new ui.Box(options);
                    this.$element = this.container.element;
                    this.element.addClass('ui-navigation');
                    this.events = js.common.mix({
                        // Respond to a navigation action (element click)!
                        // => ( id: string, event: any, widget: Navigation )
                        action: {}
                    }, this.events);
                    for (var item in items) {
                        if (items.hasOwnProperty(item)) {
                            this.container.push(this.produce(items[item], item), item);
                        }
                    }
                }
                Navigation.prototype.produce = function (title, id) {
                    var _this = this;
                    var button = new ui.Button({ flat: true, label: title, style: this.style });
                    button.connect('click', function (e) {
                        _this.trigger('action', [id, e]);
                    });
                    return button;
                };
                return Navigation;
            })(ui.Widget);
            ui.Navigation = Navigation;
        })(ui = js.ui || (js.ui = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
