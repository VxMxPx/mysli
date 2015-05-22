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
                        animation: Stack.ANI_SLIDE_LEFT
                    });
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
                    if (this.current !== id) {
                        if (this.current) {
                            this.animate(this.get(this.current, true), 'hide');
                        }
                        this.current = id;
                        this.animate(this.get(id, true), 'show');
                    }
                };
                /**
                 * Animate cell(s)
                 * @param cell
                 * @param type
                 */
                Stack.prototype.animate = function (cell, type) {
                    cell.element.css('position', 'absolute');
                    cell.element['fade' + (type === 'show' ? 'In' : 'Out')]({
                        always: function () {
                            cell.element.css('position', 'relative');
                        }
                    });
                };
                return Stack;
            })(ui.Container);
            ui.Stack = Stack;
        })(ui = js.ui || (js.ui = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
