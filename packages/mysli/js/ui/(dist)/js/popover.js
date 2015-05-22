/// <reference path="container.ts" />
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
            var Popover = (function (_super) {
                __extends(Popover, _super);
                function Popover(options) {
                    var _this = this;
                    if (options === void 0) { options = {}; }
                    _super.call(this, options);
                    // Weather popover is visible at the moment
                    this.visible = false;
                    this.element.addClass('ui-popover');
                    this.prop.def({
                        // Preferred position(s)
                        position: [
                            Popover.POSITION_TOP,
                            Popover.POSITION_BOTTOM,
                            Popover.POSITION_LEFT,
                            Popover.POSITION_RIGHT
                        ],
                        // Weather to center relative to the cursor / element
                        center: true,
                        // Force popout width
                        width: null,
                        // Weather to show pointer (v)
                        pointer: true,
                        // Margin [top, left], can be negative
                        margin: [0, 0]
                    });
                    this.prop.push(options, ['position', 'center', 'width', 'pointer!', 'margin']);
                    // Append element to the body
                    this.element.appendTo('body');
                    // Register events to hide popover when clicked outside
                    $('body').on('click.internal-' + this.uid, function (e) {
                        // Nothing to do if not visible...
                        if (!_this.visible) {
                            e.stopPropagation();
                        }
                        // Hide if clicked outside this element
                        if (!_this.element.is(e.target) &&
                            _this.element.has(e.target).length === 0) {
                            _this.hide();
                        }
                    });
                }
                Object.defineProperty(Popover, "POSITION_TOP", {
                    // Placement consts
                    get: function () { return 'top'; },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Popover, "POSITION_LEFT", {
                    get: function () { return 'left'; },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Popover, "POSITION_RIGHT", {
                    get: function () { return 'right'; },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Popover, "POSITION_BOTTOM", {
                    get: function () { return 'bottom'; },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Popover.prototype, "center", {
                    // Get/set center
                    get: function () {
                        return this.prop.center;
                    },
                    set: function (value) {
                        this.prop.center = value;
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Popover.prototype, "width", {
                    // Get/set width
                    get: function () {
                        return this.prop.width;
                    },
                    set: function (value) {
                        this.prop.width = value;
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Popover.prototype, "position", {
                    // Get/set place
                    get: function () {
                        return this.prop.position;
                    },
                    set: function (where) {
                        var available = ['top', 'left', 'bottom', 'right'];
                        if (typeof where === 'string' || where.length < 4) {
                            if (typeof where === 'string') {
                                where = [where];
                            }
                            this.prop.position = where;
                            for (var i = available.length - 1; i >= 0; i--) {
                                if (where.indexOf(available[i]) > -1) {
                                    continue;
                                }
                                this.prop.position.push(available[i]);
                            }
                        }
                        else {
                            this.prop.position = where;
                        }
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Popover.prototype, "pointer", {
                    // Get/set pointer
                    get: function () {
                        return this.prop.pointer;
                    },
                    set: function (value) {
                        this.prop.pointer = value;
                        if (value) {
                            this.element.addClass('pointer');
                        }
                        else {
                            this.element.removeClass('pointer');
                        }
                    },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Popover.prototype, "margin", {
                    // Get/set margin
                    get: function () {
                        return this.prop.margin;
                    },
                    set: function (to) {
                        this.prop.margin = to;
                    },
                    enumerable: true,
                    configurable: true
                });
                /**
                 * Set place on element.
                 * See show for more information.
                 * @param placement
                 */
                Popover.prototype.place = function (placement) {
                    // Declare variables
                    var element_dimension = {
                        width: this.element.outerWidth(),
                        height: this.element.outerHeight()
                    };
                    var window_dimension = {
                        width: $(window).width(),
                        height: $(window).height() /*+ $(document).scrollTop()*/
                    };
                    var final_placement;
                    // Remove position classes
                    this.element.removeClass('top left bottom right');
                    // Cursor's position (event)
                    if (typeof placement.pageX !== 'undefined') {
                        placement = {
                            top: {
                                top: placement.pageY,
                                // Adjust for center
                                left: this.prop.center ? placement.pageX : placement.pageX + parseInt(String(element_dimension.width / 2), 10)
                            }
                        };
                        placement.bottom = placement.top;
                        placement.left = placement.top;
                        placement.right = placement.top;
                    }
                    else if (placement instanceof ui.Widget) {
                        // Element's position
                        var top = placement.element.offset().top;
                        var left = placement.element.offset().left;
                        var width = placement.element.outerWidth();
                        var height = placement.element.outerHeight();
                        placement = {};
                        // Calculate top point
                        placement.top = {
                            top: top,
                            left: this.prop.center ? left + parseInt(String(width / 2), 10) : left
                        };
                        // Calculate bottom point
                        placement.bottom = {
                            top: top + height,
                            left: this.prop.center ? left + parseInt(String(width / 2), 10) : left
                        };
                        // Calculate left point
                        placement.left = {
                            top: this.prop.center ? top + parseInt(String(height / 2), 10) : top,
                            left: left
                        };
                        // Calculate right point
                        placement.right = {
                            top: this.prop.center ? top + parseInt(String(height / 2), 10) : top,
                            left: left + width
                        };
                    }
                    else if (typeof placement.top === 'number') {
                        // Number, we have valid absolute point
                        placement = {
                            top: placement
                        };
                        placement.bottom = placement.top;
                        placement.left = placement.top;
                        placement.right = placement.top;
                    }
                    else {
                        throw new Error('You need to provide a valid placement.');
                    }
                    // Try to set placement now
                    for (var i = 0, l = this.prop.position.length; i < l; i++) {
                        var current = this.prop.position[i];
                        if (current === Popover.POSITION_TOP) {
                            if (placement.top.top - element_dimension.height < $(document).scrollTop()) {
                                continue;
                            }
                            if (placement.top.left + parseInt(String(element_dimension.width / 2), 10) > window_dimension.width) {
                                continue;
                            }
                            if (placement.top.left - parseInt(String(element_dimension.width / 2), 10) < 0) {
                                continue;
                            }
                            final_placement = {
                                top: placement.top.top,
                                left: placement.top.left,
                                position: Popover.POSITION_TOP
                            };
                            break;
                        }
                        if (current === Popover.POSITION_BOTTOM) {
                            if (placement.bottom.top + element_dimension.height > window_dimension.height) {
                                continue;
                            }
                            if (placement.bottom.left + parseInt(String(element_dimension.width / 2), 10) > window_dimension.width) {
                                continue;
                            }
                            if (placement.bottom.left - parseInt(String(element_dimension.width / 2), 10) < 0) {
                                continue;
                            }
                            final_placement = {
                                left: placement.bottom.left,
                                top: placement.bottom.top,
                                position: Popover.POSITION_BOTTOM
                            };
                            break;
                        }
                        if (current === Popover.POSITION_LEFT) {
                            if (placement.left.top - parseInt(String(element_dimension.height / 2), 10) < $(document).scrollTop()) {
                                continue;
                            }
                            if (placement.left.top + parseInt(String(element_dimension.height / 2), 10) > window_dimension.height) {
                                continue;
                            }
                            if (placement.left.left < 0) {
                                continue;
                            }
                            final_placement = {
                                left: placement.left.left,
                                top: placement.left.top,
                                position: Popover.POSITION_LEFT
                            };
                            break;
                        }
                        if (current === Popover.POSITION_RIGHT) {
                            if (placement.right.top - parseInt(String(element_dimension.height / 2), 10) < $(document).scrollTop()) {
                                continue;
                            }
                            if (placement.right.left + element_dimension.width > window_dimension.width) {
                                continue;
                            }
                            if (placement.right.top + parseInt(String(element_dimension.height / 2), 10) > window_dimension.height) {
                                continue;
                            }
                            final_placement = {
                                left: placement.right.left,
                                top: placement.right.top,
                                position: Popover.POSITION_RIGHT
                            };
                            break;
                        }
                    }
                    // If we was unable to calculate actual placement,
                    // then we'll use the default one.
                    if (typeof final_placement.position === 'undefined') {
                        final_placement = {
                            left: placement[this.prop.position[0]].left,
                            top: placement[this.prop.position[0]].top,
                            position: this.prop.position[0]
                        };
                    }
                    // Apply margin
                    final_placement['top'] += this.prop.margin[0];
                    final_placement['left'] += this.prop.margin[1];
                    // Finally position element accordingly...
                    if (final_placement['position'] === Popover.POSITION_TOP) {
                        final_placement['top'] -= element_dimension.height;
                        final_placement['left'] -= parseInt(String(element_dimension.width / 2), 10);
                        this.element.css({
                            top: final_placement['top'],
                            left: final_placement['left']
                        }).addClass('top');
                    }
                    else if (final_placement['position'] === Popover.POSITION_LEFT) {
                        final_placement['top'] -= parseInt(String(element_dimension.height / 2), 10);
                        final_placement['left'] -= element_dimension.width;
                        this.element.css({
                            top: final_placement['top'],
                            left: final_placement['left']
                        }).addClass('left');
                    }
                    else if (final_placement['position'] === Popover.POSITION_BOTTOM) {
                        final_placement['left'] -= parseInt(String(element_dimension.width / 2), 10);
                        this.element.css({
                            top: final_placement['top'],
                            left: final_placement['left']
                        }).addClass('bottom');
                    }
                    else if (final_placement['position'] === Popover.POSITION_RIGHT) {
                        final_placement['top'] -= parseInt(String(element_dimension.height / 2), 10);
                        this.element.css({
                            top: final_placement['top'],
                            left: final_placement['left']
                        }).addClass('right');
                    }
                    return final_placement;
                };
                /**
                 * Show the popover.
                 * @param align_to use one of the following:
                 *   Click event (e), to position to mouse cursor,
                 *   Widget, to position to widget
                 *   Array [top, left]
                 */
                Popover.prototype.show = function (align_to) {
                    var _this = this;
                    var placement;
                    var animation = {
                        opacity: 1
                    };
                    if (this.visible) {
                        return;
                    }
                    // Costume width?
                    if (this.prop.width) {
                        this.element.width(this.prop.width);
                    }
                    // Place element to the correct placement
                    // Element MUST be appended before placed.
                    placement = this.place(align_to);
                    // Animate position a bit
                    switch (placement.position) {
                        case Popover.POSITION_TOP:
                            animation['top'] = (placement.top - 10) + 'px';
                            break;
                        case Popover.POSITION_BOTTOM:
                            animation['top'] = (placement.top + 10) + 'px';
                            break;
                        case Popover.POSITION_LEFT:
                            animation['left'] = (placement.left - 10) + 'px';
                            break;
                        case Popover.POSITION_RIGHT:
                            animation['left'] = (placement.left + 10) + 'px';
                            break;
                    }
                    this.element.css('display', 'block');
                    this.element.animate(animation, {
                        always: function () {
                            _this.visible = true;
                        }
                    });
                    // this.visible = true;
                };
                /**
                 * Hide the popover.
                 */
                Popover.prototype.hide = function () {
                    var _this = this;
                    if (!this.visible) {
                        return;
                    }
                    this.element.animate({
                        opacity: 0,
                        top: "-=10px"
                    }, {
                        always: function () {
                            _this.element.hide();
                            _this.visible = false;
                        }
                    });
                    /*, {
                        always: () => {
                            this.visible = false;
                            // See `show` method.
                            this.element.remove();
                            $('body').off('click.internal-' + this.uid);
                        }
                    }*/
                };
                return Popover;
            })(ui.Container);
            ui.Popover = Popover;
        })(ui = js.ui || (js.ui = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
