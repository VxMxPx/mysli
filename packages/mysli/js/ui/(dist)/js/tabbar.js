/// <reference path="widget.ts" />
/// <reference path="box.ts" />
/// <reference path="button.ts" />
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
            var Tabbar = (function (_super) {
                __extends(Tabbar, _super);
                function Tabbar(items, options) {
                    if (options === void 0) { options = {}; }
                    _super.call(this, options);
                    this.prop.def({
                        // Which tab is active at the moment
                        active: null
                    });
                    this.prop.push(options);
                    options.orientation = ui.Box.VERTICAL;
                    this.container = new ui.Box(options);
                    this.$element = this.container.element;
                    this.element.addClass('ui-tabbar');
                    this.events = js.common.mix({
                        // Respond to a tabbar action (tab click)
                        // => ( id: string, event: any, widget: Tabbar)
                        action: {}
                    });
                    for (var item in items) {
                        if (items.hasOwnProperty(item))
                            this.container.push(this.produce(items[item], item), item);
                    }
                }
                Object.defineProperty(Tabbar.prototype, "active", {
                    // Get/set active tab
                    get: function () {
                        return this.prop.active;
                    },
                    set: function (value) {
                        if (this.container.has(value)) {
                            if (this.prop.active)
                                this.container.get(this.prop.active).pressed = false;
                            this.prop.active = value;
                            this.container.get(value).pressed = true;
                        }
                    },
                    enumerable: true,
                    configurable: true
                });
                Tabbar.prototype.produce = function (title, id) {
                    var _this = this;
                    var button = new ui.Button({ uid: id, toggle: true, label: title, flat: true, style: this.style });
                    if (this.prop.active === id)
                        button.pressed = true;
                    button.connect('click', function (e) {
                        _this.active = button.uid;
                        _this.trigger('action', [id, e]);
                    });
                    return button;
                };
                return Tabbar;
            })(ui.Widget);
            ui.Tabbar = Tabbar;
        })(ui = js.ui || (js.ui = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
