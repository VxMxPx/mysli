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
                    this.$element = $cell;
                    this.prop = new js.common.Prop({
                        // Weather cell is visible
                        visible: true,
                        // Cell's padding
                        padding: false,
                        // Cell's border
                        border: false,
                        // Weather content should be filled to full width
                        fill: false,
                        // Where to align cell's content
                        align: Cell.ALIGN_LEFT,
                        // Weather content can be scrolled
                        scroll: Cell.SCROLL_NONE
                    }, this);
                    this.prop.push(options, ['visible', 'padding', 'border', 'fill', 'align', 'scroll']);
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
                Object.defineProperty(Cell, "ALIGN_LEFT", {
                    get: function () { return 'left'; },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Cell, "ALIGN_RIGHT", {
                    get: function () { return 'right'; },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Cell.prototype, "element", {
                    // Element
                    get: function () {
                        return this.$element;
                    },
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
                    this.element.animate(what, duration, callback);
                };
                Object.defineProperty(Cell.prototype, "padding", {
                    // Get/set padded
                    get: function () {
                        return this.prop.padding;
                    },
                    set: function (value) {
                        var positions = ['top', 'right', 'bottom', 'left'];
                        var current;
                        this.element.css('padding', '');
                        // Value is Boolean e.g.: element.padding = false
                        if (typeof value === 'boolean') {
                            value = { top: value, right: value, bottom: value, left: value };
                        }
                        // Map values
                        for (var i = 0; i < positions.length; i++) {
                            if (typeof value[i] !== 'undefined') {
                                current = value[i];
                            }
                            else if (typeof value[positions[i]] !== 'undefined') {
                                current = value[positions[i]];
                            }
                            else {
                                current = null;
                            }
                            if (typeof current === 'number') {
                                this.element.css("padding-" + positions[i], current);
                            }
                            else {
                                this.element[current ? 'addClass' : 'removeClass']("padding-" + positions[i]);
                            }
                        }
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Cell.prototype, "border", {
                    // Get/set border
                    get: function () {
                        return this.prop.border;
                    },
                    set: function (value) {
                        var positions = ['top', 'right', 'bottom', 'left'];
                        var current;
                        // Value is Boolean e.g.: element.border = false
                        if (typeof value === 'boolean') {
                            value = { top: value, right: value, bottom: value, left: value };
                        }
                        // Map values
                        for (var i = 0; i < positions.length; i++) {
                            if (typeof value[i] !== 'undefined') {
                                current = value[i];
                            }
                            else if (typeof value[positions[i]] !== 'undefined') {
                                current = value[positions[i]];
                            }
                            else {
                                current = null;
                            }
                            this.element[current ? 'addClass' : 'removeClass']("border-" + positions[i]);
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
                        this.prop.visible = status;
                        this.element[status ? 'show' : 'hide']();
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Cell.prototype, "align", {
                    // Get/set align
                    get: function () {
                        return this.prop.align;
                    },
                    set: function (value) {
                        this.prop.align = value;
                        if (value === Cell.ALIGN_LEFT) {
                            this.element.removeClass('align-right');
                            this.element.addClass('align-left');
                        }
                        else {
                            this.element.addClass('align-right');
                            this.element.removeClass('align-left');
                        }
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Cell.prototype, "fill", {
                    // Get/set fill
                    get: function () {
                        return this.prop.fill;
                    },
                    set: function (value) {
                        this.prop.fill = value;
                        this.element[value ? 'addClass' : 'removeClass']('content-fill');
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
                                this.element.addClass('scroll-x');
                                this.element.removeClass('scroll-y');
                                this.prop.scroll = value;
                                break;
                            case Cell.SCROLL_Y:
                                this.element.addClass('scroll-y');
                                this.element.removeClass('scroll-x');
                                this.prop.scroll = value;
                                break;
                            case Cell.SCROLL_BOTH:
                                this.element.removeClass('scroll-x');
                                this.element.removeClass('scroll-y');
                                this.prop.scroll = value;
                                break;
                            case Cell.SCROLL_BOTH:
                                this.element.addClass('scroll-x');
                                this.element.addClass('scroll-y');
                                this.prop.scroll = value;
                                break;
                            default:
                                throw new Error("Invalid value required: Cell.(SCROLL_X|SCROLL_Y|SCROLL_BOTH|SCROLL_NONE)");
                        }
                    },
                    enumerable: true,
                    configurable: true
                });
                /**
                 * Remove cell from a collection.
                 */
                Cell.prototype.remove = function () {
                    this.element.remove();
                };
                return Cell;
            })();
            ui.Cell = Cell;
        })(ui = js.ui || (js.ui = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
