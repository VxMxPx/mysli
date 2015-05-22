/// <reference path="container.ts" />
/// <reference path="cell.ts" />
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
            var Box = (function (_super) {
                __extends(Box, _super);
                function Box(options) {
                    if (options === void 0) { options = {}; }
                    _super.call(this, options);
                    this.Cell_constructor = BoxCell;
                    this.prop.def({
                        orientation: Box.HORIZONTAL
                    });
                    this.prop.push(options);
                    this.element.addClass('ui-box');
                    this.element_wrapper_original = this.element_wrapper;
                    if (this.prop.orientation === Box.VERTICAL) {
                        var row = $('<div class="ui-row" />');
                        this.element.append(row);
                        this.element.addClass('ui-orientation-vertical');
                        this.$target = row;
                    }
                    else {
                        this.element.addClass('ui-orientation-horizontal');
                    }
                }
                Object.defineProperty(Box, "HORIZONTAL", {
                    get: function () { return 1; },
                    enumerable: true,
                    configurable: true
                });
                Object.defineProperty(Box, "VERTICAL", {
                    get: function () { return 2; },
                    enumerable: true,
                    configurable: true
                });
                /**
                 * Override insert, to support horizontal/vertical layout.
                 */
                Box.prototype.insert = function () {
                    var args = [];
                    for (var _i = 0; _i < arguments.length; _i++) {
                        args[_i - 0] = arguments[_i];
                    }
                    if (this.prop.orientation === Box.HORIZONTAL) {
                        this.element_wrapper = '<div class="ui-row"><div class="ui-cell container-target" /></div>';
                    }
                    else {
                        this.element_wrapper = this.element_wrapper_original;
                    }
                    return _super.prototype.insert.apply(this, args);
                };
                return Box;
            })(ui.Container);
            ui.Box = Box;
            var BoxCell = (function (_super) {
                __extends(BoxCell, _super);
                function BoxCell(parent, $cell, options) {
                    if (options === void 0) { options = {}; }
                    _super.call(this, parent, $cell, options);
                    this.prop.def({ expanded: false });
                    this.prop.push(options, ['expanded']);
                }
                Object.defineProperty(BoxCell.prototype, "expanded", {
                    // Get/set expanded
                    get: function () {
                        return this.prop.expanded;
                    },
                    set: function (value) {
                        this.$element[value ? 'addClass' : 'removeClass']('expanded');
                    },
                    enumerable: true,
                    configurable: true
                });
                return BoxCell;
            })(ui.Cell);
        })(ui = js.ui || (js.ui = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
