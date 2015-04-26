/// <reference path="container.ts" />
/// <reference path="_inc.common.ts" />
var mysli;
(function (mysli) {
    var js;
    (function (js) {
        var ui;
        (function (ui) {
            var Cell = (function () {
                function Cell(parent, $cell) {
                    this.prop = {};
                    this.parent = parent;
                    this.$cell = $cell;
                    this.prop.visible = true;
                }
                /**
                 * Animate the cell.
                 * @param {any}    what
                 * @param {number} duration
                 * @param {any}    callback
                 */
                Cell.prototype.animate = function (what, duration, callback) {
                    if (duration === void 0) { duration = 500; }
                    if (callback === void 0) { callback = false; }
                    this.$cell.animate(what, duration, callback);
                };
                /**
                 * Change cell visibility
                 * @param  {boolean}  status
                 * @return {boolean}
                 */
                Cell.prototype.visible = function (status) {
                    if (typeof status !== 'undefined' && status !== this.prop.visible) {
                        this.prop.visible = status;
                        if (status) {
                            this.$cell.show();
                        }
                        else {
                            this.$cell.hide();
                        }
                    }
                    return this.prop.visible;
                };
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
