var __extends = this.__extends || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    __.prototype = b.prototype;
    d.prototype = new __();
};
/// <reference path="widget.ts" />
/// <reference path="cell.ts" />
/// <reference path="_arr.ts" />
var mysli;
(function (mysli) {
    var js;
    (function (js) {
        var ui;
        (function (ui) {
            var Container = (function (_super) {
                __extends(Container, _super);
                function Container(options) {
                    if (options === void 0) { options = {}; }
                    _super.call(this, options);
                    this.collection = new ui.Arr();
                    this.element().addClass('ui-container');
                    this.$target = this.element();
                }
                /**
                 * Push widget to the contaner
                 * @param  {Widget} element
                 * @param  {string} uid
                 * @return {Widget}
                 */
                Container.prototype.push = function (widget, uid) {
                    if (uid === void 0) { uid = null; }
                    return this.insert(widget, -1, uid);
                };
                /**
                 * Insert widget to the container.
                 * @param  {Widget} widget
                 * @param  {number} at
                 * @param  {string} uid
                 * @return {Widget}
                 */
                Container.prototype.insert = function (widget, at, uid) {
                    if (uid === void 0) { uid = null; }
                    var at_index;
                    var class_id;
                    var pushable;
                    if (!(widget instanceof ui.Widget)) {
                        throw new Error('Instance of widget is required!');
                    }
                    // If no UID is provided, the element's uid will be used
                    if (uid === null) {
                        uid = widget.uid();
                    }
                    // Set collection uid (which might be different from uid itself)
                    // element.collection_uid = uid;
                    // Either push after another element or at the end of the list
                    if (at > -1) {
                        at_index = this.collection.push_after(at, uid, widget);
                    }
                    else {
                        at_index = this.collection.push(uid, widget);
                    }
                    // If costume allows us to continue
                    class_id = 'coll-euid-' + widget.uid() + ' coll-uid-' + uid;
                    // Create wrapper, append at the end of the list
                    if (this.constructor['element_wrapper']) {
                        pushable = $(this.constructor['element_wrapper']);
                        pushable.addClass(class_id);
                        if (pushable.filter('.container-target').length) {
                            pushable.filter('.container-target').append(widget.element());
                        }
                        else if (pushable.find('.container-target').length) {
                            pushable.find('.container-target').append(widget.element());
                        }
                        else {
                            throw new Error("Cannot find .container-target!");
                        }
                    }
                    else {
                        widget.element().addClass(class_id);
                        pushable = widget.element();
                    }
                    // Either inster after particular element or just at the end
                    if (at > -1) {
                        this.$target
                            .find('.coll-euid-' + this.collection.get_from(at_index, -1).uid())
                            .after(pushable);
                    }
                    else {
                        this.$target.append(pushable);
                    }
                    return widget;
                };
                /**
                * Get elements from the collection. If `cell` is provided, get cell itself.
                * @param  {string|number} uid  either string (uid) or number (index)
                * @param  {boolean}       cell weather to get cell itself rather than containing element.
                * @return {any}
                */
                Container.prototype.get = function (uid, cell) {
                    if (cell && this.constructor['element_wrapper']) {
                        uid = '.coll-euid-' + this.collection.get(uid).uid();
                        return new ui.Cell(this, this.$target.find(uid));
                    }
                    else {
                        return this.collection.get(uid);
                    }
                };
                /**
                 * Remove particular cell (and the containing element)
                 * @param {string|number} uid
                 */
                Container.prototype.remove = function (uid) {
                    uid = this.collection.get(uid).uid();
                    this.collection.remove(uid);
                    this.$target.find('.coll-euid-' + uid).remove();
                };
                Container.element_wrapper = '<div class="ui-cell container-target" />';
                return Container;
            })(ui.Widget);
            ui.Container = Container;
        })(ui = js.ui || (js.ui = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
