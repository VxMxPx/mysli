/// <reference path="container.ts" />
/// <reference path="_inc.common.ts" />
var mysli;
(function (mysli) {
    var js;
    (function (js) {
        var ui;
        (function (ui) {
            var Cell = (function () {
                function Cell(parent, $cell, options) {
                    if (options === void 0) { options = {}; }
                    this.parent = parent;
                    this.$cell = $cell;
                    this.prop = new js.common.Prop({
                        visible: true,
                        padding: false,
                        scroll: Cell.SCROLL_NONE
                    }, this);
                    this.prop.push(options, ['visible', 'padding', 'scroll']);
                }
                Object.defineProperty(Cell, "SCROLL_Y", {
                    get: function () { return 'scroll-y'; },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Cell, "SCROLL_X", {
                    get: function () { return 'scroll-x'; },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Cell, "SCROLL_BOTH", {
                    get: function () { return 'scroll-both'; },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Cell, "SCROLL_NONE", {
                    get: function () { return 'scroll-none'; },
                    enumerable: true,
                    configurable: true
                });
                /**
                 * Animate the cell.
                 * @param what
                 * @param duration
                 * @param callback
                 */
                Cell.prototype.animate = function (what, duration, callback) {
                    if (duration === void 0) { duration = 500; }
                    if (callback === void 0) { callback = false; }
                    this.$cell.animate(what, duration, callback);
                };
                Object.defineProperty(Cell.prototype, "padding", {
                    // Get/set padded
                    get: function () {
                        return this.prop.padding;
                    },
                    set: function (value) {
                        var positions = ['top', 'right', 'bottom', 'left'];
                        this.$cell.css('padding', '');
                        if (typeof value === 'boolean')
                            value = [value, value, value, value];
                        for (var i = 0; i < positions.length; i++) {
                            if (typeof value[i] === 'number')
                                this.$cell.css("padding-" + positions[i], value[i]);
                            else
                                this.$cell[value[i] ? 'addClass' : 'removeClass']("pad" + positions[i]);
                        }
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Cell.prototype, "visible", {
                    // Get/set visibility
                    get: function () {
                        return this.prop.visible;
                    },
                    set: function (status) {
                        if (status === this.prop.visible)
                            return;
                        this.prop.visible = status;
                        this.$cell[status ? 'show' : 'hide']();
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Cell.prototype, "scroll", {
                    // Get/set scroll
                    get: function () {
                        return this.prop.scroll;
                    },
                    set: function (value) {
                        switch (value) {
                            case Cell.SCROLL_X:
                                this.$cell.addClass('scroll-x');
                                this.$cell.removeClass('scroll-y');
                                this.prop.scroll = value;
                                break;
                            case Cell.SCROLL_Y:
                                this.$cell.addClass('scroll-y');
                                this.$cell.removeClass('scroll-x');
                                this.prop.scroll = value;
                                break;
                            case Cell.SCROLL_BOTH:
                                this.$cell.removeClass('scroll-x');
                                this.$cell.removeClass('scroll-y');
                                this.prop.scroll = value;
                                break;
                            case Cell.SCROLL_BOTH:
                                this.$cell.addClass('scroll-x');
                                this.$cell.addClass('scroll-y');
                                this.prop.scroll = value;
                                break;
                            default:
                                throw new Error("Invalid value required: Cell.(SCROLL_X|SCROLL_Y|SCROLL_BOTH|SCROLL_NONE)");
                                break;
                        }
                    },
                    enumerable: true,
                    configurable: true
                });
                /**
                 * Remove cell from a collection.
                 */
                Cell.prototype.remove = function () {
                    this.$cell.remove();
                };
                return Cell;
            })();
            ui.Cell = Cell;
        })(ui = js.ui || (js.ui = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
