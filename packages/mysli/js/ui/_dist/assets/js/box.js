var __extends = this.__extends || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    __.prototype = b.prototype;
    d.prototype = new __();
};
/// <reference path="container.ts" />
/// <reference path="_jquery.d.ts" />
var mysli;
(function (mysli) {
    var js;
    (function (js) {
        var ui;
        (function (ui) {
            var Box = (function (_super) {
                __extends(Box, _super);
                function Box(options) {
                    if (options === void 0) { options = {}; }
                    _super.call(this, options);
                    // Apply own defaults first...
                    this.prop = ui.Util.mix({
                        orientation: 'horizontal'
                    }, this.prop);
                    this.element().addClass('ui-box');
                    Box.element_wrapper = ui.Container.element_wrapper;
                    if (this.prop.orientation === 'vertical') {
                        var row = $('<div class="ui-row" />');
                        this.element().append(row);
                        this.$target = row;
                    }
                }
                /**
                 * Override get, to support expanded method.
                 */
                Box.prototype.get = function () {
                    var result = _super.prototype.get.apply(this, arguments);
                    if (result instanceof ui.Cell) {
                        result.expanded = Box.expanded.bind(result);
                    }
                    return result;
                };
                /**
                 * Override insert, to support horizontal/vertical layout.
                 */
                Box.prototype.insert = function () {
                    if (this.prop.orientation === 'horizontal') {
                        Box.element_wrapper = '<div class="ui-row"><div class="ui-cell container-target" /></div>';
                    }
                    else {
                        Box.element_wrapper = this.element_wrapper_original;
                    }
                    return _super.prototype.insert.apply(this, arguments);
                };
                /**
                 * Method to be appended to the Cell object.
                 * @param  {boolean} status
                 * @return {boolean}
                 */
                Box.expanded = function (status) {
                    if (typeof status !== 'undefined') {
                        this.$cell[status ? 'addClass' : 'removeClass']('cell-expanded');
                    }
                    return this.$cell.hasClass('cell-expanded');
                };
                return Box;
            })(ui.Container);
            ui.Box = Box;
        })(ui = js.ui || (js.ui = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
