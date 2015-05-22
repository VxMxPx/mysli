/// <reference path="widget.ts" />
/// <reference path="box.ts" />
/// <reference path="button.ts" />
/// <reference path="stack.ts" />
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
                function Tabbar(options) {
                    if (options === void 0) { options = {}; }
                    _super.call(this, options);
                    // Construct container
                    options.orientation = ui.Box.VERTICAL;
                    this.container = new ui.Box(options);
                    // Register events...
                    this.events = js.common.mix({
                        // Respond to a tabbar action (tab click)
                        // => ( id: string, event: any, widget: Tabbar)
                        action: {}
                    });
                    // Set defaults
                    this.prop.def({
                        // Which tab is active at the moment
                        active: null,
                        // Stack, to which tab will be connected...
                        stack: null
                    });
                    this.prop.push(options, ['stack']);
                    // Set proper element and class
                    this.$element = this.container.element;
                    this.element.addClass('ui-tabbar');
                    // Push options if any
                    if (typeof options.add !== 'undefined') {
                        this.add(options.add);
                    }
                }
                Object.defineProperty(Tabbar.prototype, "stack", {
                    // Get/set stack
                    get: function () {
                        return this.prop.stack;
                    },
                    set: function (stack) {
                        this.prop.stack = stack;
                        this.container.each(function (_, widget) {
                            if (!stack.has(widget.uid)) {
                                stack.push(new ui.Container(), widget.uid);
                            }
                        });
                        if (this.active) {
                            this.stack.to(this.active);
                        }
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Tabbar.prototype, "active", {
                    // Get/set active tab
                    get: function () {
                        return this.prop.active;
                    },
                    set: function (value) {
                        if (this.container.has(value)) {
                            if (this.prop.active) {
                                this.container.get(this.prop.active).pressed = false;
                            }
                            this.prop.active = value;
                            this.container.get(value).pressed = true;
                        }
                    },
                    enumerable: true,
                    configurable: true
                });
                /**
                 * Add items to the container
                 * @param items in format: {id: Label, id: Label}
                 */
                Tabbar.prototype.add = function (items) {
                    for (var item in items) {
                        if (items.hasOwnProperty(item)) {
                            if (this.stack) {
                                if (!this.stack.has(item)) {
                                    this.stack.push(new ui.Container(), item);
                                }
                            }
                            this.container.push(this.produce(items[item], item), item);
                        }
                    }
                };
                /**
                 * Produce a new tab element.
                 * @param title
                 * @param id
                 */
                Tabbar.prototype.produce = function (title, id) {
                    var _this = this;
                    var button = new ui.Button({
                        uid: id,
                        toggle: true,
                        label: title,
                        flat: true,
                        style: this.style
                    });
                    if (this.prop.active === id) {
                        button.pressed = true;
                        if (this.stack) {
                            this.stack.to(id);
                        }
                    }
                    button.connect('click', function (e) {
                        if (_this.stack) {
                            _this.stack.to(button.uid);
                        }
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
