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
                    // Set default values
                    this.prop = {
                        visible: true
                    };
                    // Apply new values if needed
                    this.visible = options.visible || true;
                }
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
                Object.defineProperty(Cell.prototype, "visible", {
                    // Get/set visibility
                    get: function () {
                        return this.prop.visible;
                    },
                    set: function (status) {
                        if (status === this.prop.visible) {
                            return;
                        }
                        this.prop.visible = status;
                        this.$cell[status ? 'show' : 'hide']();
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
