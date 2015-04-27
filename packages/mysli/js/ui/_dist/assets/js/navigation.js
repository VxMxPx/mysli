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
                    this.collection = new js.common.Arr();
                    this.container = new ui.Box(options);
                    this.$element = this.container.element;
                    this.element.addClass('ui-navigation');
                    this.events = js.common.mix({
                        // Respond to a navigation element click!
                        // => ( id: string, event: any, navigation: Navigation )
                        action: {}
                    }, this.events);
                    for (var item in items) {
                        if (items.hasOwnProperty(item)) {
                            this.container.push(Navigation.produce(items[item], item, this), item);
                        }
                    }
                }
                Navigation.produce = function (title, id, sender) {
                    var button = new ui.Button({ label: title });
                    button.connect('click', function (e) {
                        this.trigger('action', [id, e]);
                    }.bind(sender));
                    return button;
                };
                return Navigation;
            })(ui.Widget);
            ui.Navigation = Navigation;
        })(ui = js.ui || (js.ui = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
