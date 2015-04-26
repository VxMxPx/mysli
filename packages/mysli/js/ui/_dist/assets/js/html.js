var __extends = this.__extends || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    __.prototype = b.prototype;
    d.prototype = new __();
};
/// <reference path="widget.ts" />
/// <reference path="_inc.common.ts" />
var mysli;
(function (mysli) {
    var js;
    (function (js) {
        var ui;
        (function (ui) {
            var HTML = (function (_super) {
                __extends(HTML, _super);
                function HTML(options) {
                    _super.call(this, options);
                    this.element.addClass('ui-html');
                }
                /**
                 * Push new HTML to the container.
                 * @param  {string} html
                 * @param  {string} uid
                 * @return {JQuery}
                 */
                HTML.prototype.push = function (html, uid) {
                    if (uid === void 0) { uid = null; }
                    var element = $(html);
                    this.element.append(element);
                    return element;
                };
                /**
                 * Remove element(s) by specific jQuery selector.
                 * @param {string} selector
                 */
                HTML.prototype.remove = function (selector) {
                    this.element.filter(selector).remove();
                };
                return HTML;
            })(ui.Widget);
            ui.HTML = HTML;
        })(ui = js.ui || (js.ui = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
