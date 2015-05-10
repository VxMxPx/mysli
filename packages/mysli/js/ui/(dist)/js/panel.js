/// <reference path="widget.ts" />
/// <reference path="panel_side.ts" />
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
            var Panel = (function (_super) {
                __extends(Panel, _super);
                function Panel(options) {
                    var _this = this;
                    if (options === void 0) { options = {}; }
                    _super.call(this, options);
                    // List of connected panels
                    // private connected: common.Arr = new common.Arr();
                    // when true, some events will be prevented on the panel, like further animations
                    this.closing = false;
                    // when panel goes to full screen highest zIndex is set, this is the
                    // original zIndex, to be restored, when full screen is turned off
                    this.old_zindex = 0;
                    this.old_width = 0;
                    this.element.addClass('ui-panel');
                    this.element.append('<div class="ui-panel-sides" />');
                    // Add supported events
                    this.events = js.common.mix({
                        // When panel `close` method is called, just before panel's
                        // `destroy` method is invoked.
                        // => ( panel: Panel )
                        'close': {},
                        // On away status change
                        // => ( status: boolean, width: number, panel: Panel )
                        'set-away': {},
                        // On size changed
                        // => ( width: number, diff: number, panel: Panel )
                        'set-width': {},
                        // On popout status changed
                        // => ( value: boolean, panel: Panel )
                        'set-popout': {},
                        // On insensitive status changed
                        // => ( value: boolean, panel: Panel )
                        'set-insensitive': {},
                        // On min_size value changed
                        // => ( min_size: number, panel: Panel )
                        'set-min-size': {},
                        // On focus changed
                        // => ( value: boolean, panel: Panel )
                        'set-focus': {},
                        // On expandable status changed
                        // => ( value: boolean, panel: Panel )
                        'set-expandable': {}
                    }, this.events);
                    this.prop.def({
                        // position in px from left
                        position: 0,
                        // when there's a lot of panels, they start being pushed aside
                        // and partly hidden
                        offset: 0,
                        // weather panel is locked
                        locked: false,
                        // weather panel can be expanded to fill the available space
                        expandable: false,
                        // how much panel's width was increased (only if expandable is true)
                        expanded_for: 0,
                        // for how much can panel shrink (if 0 it can't shrink)
                        min_size: 0,
                        // panel's size by px
                        width: Panel.SIZE_NORMAL,
                        // is panel in away mode
                        away: false,
                        // if away on blur, then panel will go away when lose focus
                        away_on_blur: false,
                        // the width (px) of panel when away
                        away_width: 10,
                        // if insensitive, then panel cannot be focused
                        insensitive: false,
                        // if panel is popout
                        popout: false,
                        // Size of the panel when popout
                        popout_size: Panel.SIZE_HUGE,
                        // Weather panel is in focus
                        focus: false,
                        // Weather panel can be flipped (back side exists!)
                        flippable: false,
                        // Which side is visible
                        side: Panel.SIDE_FRONT
                    });
                    this.prop.push(options);
                    this.element.width(this.prop.width);
                    // Proxy the click event to focus
                    this.element.on('click', function () {
                        if (!_this.prop.closing && !_this.locked) {
                            _this.focus = true;
                        }
                    });
                    // Add Sides
                    this.front = new ui.PanelSide();
                    this.element.find('.ui-panel-sides').append(this.front.element);
                    if (this.prop.flippable) {
                        // Add multi-panel class
                        this.element.addClass('multi');
                        // Add actual panel
                        this.back = new ui.PanelSide({ style: 'alt' });
                        this.element.find('.ui-panel-sides').append(this.back.element);
                        // Set desired side
                        this.side = this.prop.side;
                    }
                }
                Object.defineProperty(Panel, "SIZE_TINY", {
                    // Predefined panel sizes
                    get: function () { return 160; },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Panel, "SIZE_SMALL", {
                    get: function () { return 260; },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Panel, "SIZE_NORMAL", {
                    get: function () { return 340; },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Panel, "SIZE_BIG", {
                    get: function () { return 500; },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Panel, "SIZE_HUGE", {
                    get: function () { return 800; },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Panel, "SIDE_FRONT", {
                    get: function () { return 'front'; },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Panel, "SIDE_BACK", {
                    get: function () { return 'back'; },
                    enumerable: true,
                    configurable: true
                });
                /**
                 * Animate all the changes made to the element.
                 */
                Panel.prototype.animate = function (callback) {
                    if (this.prop.closing) {
                        return;
                    }
                    this.element.stop(true, false).animate({
                        left: this.position + this.offset,
                        width: this.width + this.expand,
                        opacity: 1
                    }, {
                        duration: 400,
                        queue: false,
                        always: function () {
                            if (callback) {
                                callback.call(this);
                            }
                        }
                    }).css({ overflow: 'visible' });
                };
                Object.defineProperty(Panel.prototype, "side", {
                    /**
                     * Get/set panel's visible side
                     */
                    get: function () {
                        return this.prop.side;
                    },
                    set: function (value) {
                        if (Panel.valid_sides.indexOf(value) === -1) {
                            throw new Error("Trying to set invalid side: " + value);
                        }
                        if (!this.prop.flippable) {
                            throw new Error("Trying to flip a panel which is not flippable.");
                        }
                        // Right now this is hard coded, there are only two sides.
                        // It's possible that in future more sides will be added?
                        this.element[value === Panel.SIDE_BACK ? 'addClass' : 'removeClass']('flipped');
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Panel.prototype, "width", {
                    /**
                     * Get/set panel's width
                     */
                    get: function () {
                        return this.prop.width;
                    },
                    set: function (value) {
                        var diff;
                        if (value === this.width) {
                            return;
                        }
                        diff = -(this.width - value);
                        this.prop.width = value;
                        this.trigger('set-width', [value, diff]);
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Panel.prototype, "away", {
                    /**
                     * Get/set away status for panel.
                     */
                    get: function () {
                        return this.prop.away;
                    },
                    set: function (status) {
                        var width;
                        if (status === this.away) {
                            return;
                        }
                        if (status) {
                            if (this.focus || this.away) {
                                this.prop.away_on_blur = true;
                                return;
                            }
                            this.prop.away = true;
                            width = -(this.width - this.away_width);
                        }
                        else {
                            if (!this.away) {
                                this.prop.away_on_blur = false;
                                return;
                            }
                            this.prop.away = false;
                            this.prop.away_on_blur = false;
                            width = this.width - this.away_width;
                        }
                        this.trigger('set-away', [status, width]);
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Panel.prototype, "popout", {
                    /**
                     * Get/set panel's popout status.
                     */
                    get: function () {
                        return this.prop.popout;
                    },
                    set: function (status) {
                        if (status === this.popout) {
                            return;
                        }
                        if (status) {
                            this.prop.popout = true;
                            this.focus = true;
                            this.old_zindex = +this.element.css('z-index');
                            this.old_width = this.width;
                            this.element.css('z-index', 10005);
                            this.width = this.prop.popout_size;
                        }
                        else {
                            this.prop.popout = false;
                            this.element.css('z-index', this.old_zindex);
                            this.width = this.old_width;
                        }
                        this.trigger('set-popout', [status]);
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Panel.prototype, "insensitive", {
                    /**
                     * Get/get insensitive status.
                     */
                    get: function () {
                        return this.prop.insensitive;
                    },
                    set: function (value) {
                        if (value === this.insensitive) {
                            return;
                        }
                        if (value) {
                            if (this.focus) {
                                this.focus = false;
                            }
                            this.prop.insensitive = true;
                        }
                        else {
                            this.prop.insensitive = false;
                        }
                        this.trigger('set-insensitive', [value]);
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Panel.prototype, "min_size", {
                    /**
                     * Get/set panel's min size.
                     */
                    get: function () {
                        return this.prop.min_size;
                    },
                    set: function (size) {
                        if (this.min_size === size) {
                            return;
                        }
                        this.prop.min_size = size;
                        this.trigger('set-min-size', [size]);
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Panel.prototype, "focus", {
                    /**
                     * Get/set focus.
                     */
                    get: function () {
                        return this.prop.focus;
                    },
                    set: function (value) {
                        if (value === this.focus) {
                            return;
                        }
                        if (value) {
                            this.prop.focus = true;
                            this.element.addClass('focused');
                            if (this.away) {
                                this.away = false;
                                this.prop.away_on_blur = true;
                            }
                        }
                        else {
                            this.prop.focus = false;
                            this.element.removeClass('focused');
                            if (this.prop.away_on_blur) {
                                this.away = true;
                            }
                        }
                        this.trigger('set-focus', [value]);
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Panel.prototype, "expandable", {
                    /**
                     * Get/set expandable status.
                     */
                    get: function () {
                        return this.prop.expandable;
                    },
                    set: function (value) {
                        if (value !== this.expandable) {
                            this.prop.expandable = value;
                            this.trigger('set-expandable', [value]);
                        }
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Panel.prototype, "position", {
                    /**
                     * Get/set panel's position
                     */
                    get: function () {
                        return this.prop.position;
                    },
                    set: function (value) {
                        this.prop.position = value;
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Panel.prototype, "offset", {
                    /**
                     * Get/set panel's offset
                     */
                    get: function () {
                        return this.prop.offset;
                    },
                    set: function (value) {
                        this.prop.offset = value;
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Panel.prototype, "locked", {
                    /**
                     * Get/set panel's locked state
                     */
                    get: function () {
                        return this.prop.locked;
                    },
                    set: function (value) {
                        this.prop.locked = value;
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Panel.prototype, "expand", {
                    /**
                     * Get/set panel's locked state
                     */
                    get: function () {
                        return this.prop.expanded_for;
                    },
                    set: function (value) {
                        this.prop.expanded_for = value;
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Panel.prototype, "away_width", {
                    /**
                     * Get away width
                     */
                    get: function () {
                        return this.prop.away_width;
                    },
                    enumerable: true,
                    configurable: true
                });
                /**
                 * Close the panel.
                 */
                Panel.prototype.close = function () {
                    var _this = this;
                    if (this.locked) {
                        return;
                    }
                    this.insensitive = true;
                    this.prop.closing = true;
                    this.trigger('close');
                    this.element.stop(true, false).animate({
                        left: (this.position + this.offset) - (this.width + this.expand) - 10,
                        opacity: 0
                    }, {
                        done: function () {
                            _this.destroy();
                        }
                    });
                };
                Panel.valid_sides = ['front', 'back'];
                return Panel;
            })(ui.Widget);
            ui.Panel = Panel;
        })(ui = js.ui || (js.ui = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
