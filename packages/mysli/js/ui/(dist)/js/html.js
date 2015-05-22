/// <reference path="widget.ts" />
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
            var HTML = (function (_super) {
                __extends(HTML, _super);
                function HTML(text, options) {
                    if (text === void 0) { text = {}; }
                    if (options === void 0) { options = {}; }
                    if (text !== null && typeof text === 'object')
                        options = text;
                    _super.call(this, options);
                    this.element.addClass('ui-html');
                    if (typeof text === 'string')
                        this.push(text);
                }
                /**
                 * Push new HTML to the container.
                 * @param html
                 */
                HTML.prototype.push = function (html) {
                    var element;
                    // Wrap HTML in a div
                    html = "<div class=\"ui-html-element\">" + html + "</div>";
                    element = $(html);
                    this.element.append(element);
                    return element;
                };
                /**
                 * Replace all content in the container, with a new content...
                 */
                HTML.prototype.replace = function (html) {
                    this.element.html('');
                    return this.push(html);
                };
                /**
                 * Remove element(s) by specific jQuery selector.
                 * @param selector
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
