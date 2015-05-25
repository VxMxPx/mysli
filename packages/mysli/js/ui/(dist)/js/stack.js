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
            var Stack = (function (_super) {
                __extends(Stack, _super);
                // METHODS
                function Stack(options) {
                    if (options === void 0) { options = {}; }
                    _super.call(this, options);
                    this.element.addClass('ui-stack');
                    this.prop.def({
                        // Which animation to use, when switching between tabs...
                        animation: Stack.ANI_SLIDE_DIRECTION_HORIZONTAL
                    });
                    this.prop.push(options, ['animation']);
                }
                Object.defineProperty(Stack, "ANI_SLIDE_UP", {
                    // CONSTANTS
                    get: function () { return 'animate-slide-up'; },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Stack, "ANI_SLIDE_DOWN", {
                    get: function () { return 'animate-slide-down'; },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Stack, "ANI_SLIDE_LEFT", {
                    get: function () { return 'animate-slide-left'; },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Stack, "ANI_SLIDE_RIGHT", {
                    get: function () { return 'animate-slide-right'; },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Stack, "ANI_SLIDE_DIRECTION_VERTICAL", {
                    get: function () { return 'animate-slide-direction-vertical'; },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Stack, "ANI_SLIDE_DIRECTION_HORIZONTAL", {
                    get: function () { return 'animate-slide-direction-horizontal'; },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Stack, "ANI_FADE", {
                    get: function () { return 'animate-fade'; },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Stack.prototype, "animation", {
                    // Get/set animation
                    get: function () {
                        return this.prop.animation;
                    },
                    set: function (value) {
                        this.element.removeClass(this.prop.animation);
                        this.element.addClass(value);
                        this.prop.animation = value;
                    },
                    enumerable: true,
                    configurable: true
                });
                /**
                 * Go to particular view.
                 * @param id
                 */
                Stack.prototype.to = function (id) {
                    var direction;
                    if (this.current !== id) {
                        if (this.current) {
                            var from = this.collection.get_index(this.current);
                            var to = this.collection.get_index(id);
                            direction = from < to ? 'positive' : 'negative';
                            this.animate(this.get(this.current, true), 'hide', this.prop.animation, direction);
                        }
                        else {
                            direction = 'positive';
                        }
                        this.current = id;
                        this.animate(this.get(id, true), 'show', this.prop.animation, direction);
                    }
                };
                /**
                 * Animate cell(s)
                 * @param cell
                 * @param visibility show|hide
                 * @param animation_type
                 * @param direction positive|negative
                 */
                Stack.prototype.animate = function (cell, visibility, animation_type, direction) {
                    var animation;
                    if (animation_type === Stack.ANI_SLIDE_DIRECTION_HORIZONTAL) {
                        animation_type = direction === 'positive' ? Stack.ANI_SLIDE_LEFT : Stack.ANI_SLIDE_RIGHT;
                    }
                    else if (animation_type === Stack.ANI_SLIDE_DIRECTION_VERTICAL) {
                        animation_type = direction === 'positive' ? Stack.ANI_SLIDE_DOWN : Stack.ANI_SLIDE_UP;
                    }
                    switch (animation_type) {
                        case Stack.ANI_FADE:
                            if (visibility === 'show') {
                                animation = [
                                    {
                                        position: 'absolute',
                                        display: 'block',
                                        opacity: 0
                                    },
                                    {
                                        opacity: 1
                                    },
                                    {
                                        position: 'relative'
                                    }
                                ];
                            }
                            else {
                                animation = [
                                    {
                                        position: 'absolute'
                                    },
                                    {
                                        opacity: 0
                                    },
                                    {
                                        display: 'none',
                                        opacity: 1,
                                        position: 'relative'
                                    }
                                ];
                            }
                            break;
                        case Stack.ANI_SLIDE_LEFT:
                        case Stack.ANI_SLIDE_RIGHT:
                            var left = [this.element.outerWidth() + 20];
                            if (animation_type === Stack.ANI_SLIDE_LEFT) {
                                left[1] = -(left[0]);
                            }
                            else {
                                left[1] = left[0];
                                left[0] = -(left[0]);
                            }
                            if (visibility === 'show') {
                                animation = [
                                    {
                                        position: 'absolute',
                                        width: this.element.width() ? this.element.width() : 'auto',
                                        display: 'block',
                                        opacity: 0,
                                        left: left[0]
                                    },
                                    {
                                        left: 0,
                                        opacity: 1
                                    },
                                    {
                                        position: 'relative'
                                    }
                                ];
                            }
                            else {
                                animation = [
                                    {
                                        position: 'absolute'
                                    },
                                    {
                                        left: left[1],
                                        opacity: 0
                                    },
                                    {
                                        position: 'relative',
                                        display: 'none',
                                        opacity: 1,
                                        left: 0
                                    }
                                ];
                            }
                            break;
                        case Stack.ANI_SLIDE_UP:
                        case Stack.ANI_SLIDE_DOWN:
                            var top = [this.element.outerHeight() + 20];
                            if (animation_type === Stack.ANI_SLIDE_UP) {
                                top[1] = -(top[0]);
                            }
                            else {
                                top[1] = top[0];
                                top[0] = -(top[0]);
                            }
                            if (visibility === 'show') {
                                animation = [
                                    {
                                        position: 'absolute',
                                        width: this.element.width() ? this.element.width() : 'auto',
                                        display: 'block',
                                        opacity: 0,
                                        top: top[0]
                                    },
                                    {
                                        top: 0,
                                        opacity: 1
                                    },
                                    {
                                        position: 'relative'
                                    }
                                ];
                            }
                            else {
                                animation = [
                                    {
                                        position: 'absolute'
                                    },
                                    {
                                        top: top[1],
                                        opacity: 0
                                    },
                                    {
                                        position: 'relative',
                                        display: 'none',
                                        opacity: 1,
                                        top: 0
                                    }
                                ];
                            }
                            break;
                    }
                    // Preform the actual animation...
                    cell.element.css(animation[0]);
                    cell.element.animate(animation[1], {
                        always: function () {
                            cell.element.css(animation[2]);
                        }
                    });
                };
                return Stack;
            })(ui.Container);
            ui.Stack = Stack;
        })(ui = js.ui || (js.ui = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
