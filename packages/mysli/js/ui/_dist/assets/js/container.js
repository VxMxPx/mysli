var __extends = this.__extends || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    __.prototype = b.prototype;
    d.prototype = new __();
};
/// <reference path="widget.ts" />
/// <reference path="cell.ts" />
/// <reference path="_inc.common.ts" />
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
                    // Allows to replace cell interface when extending this class
                    this.Cell_constructor = ui.Cell;
                    // Collection of all contained elements
                    this.collection = new js.common.Arr();
                    this.element_wrapper = '<div class="ui-cell container-target"></div>';
                    this.element.addClass('ui-container');
                    this.$target = this.element;
                }
                /**
                 * Push widget to the contaner
                 * @param widget
                 * @param options
                 */
                Container.prototype.push = function (widget, options) {
                    if (options === void 0) { options = null; }
                    return this.insert(widget, -1, options);
                };
                /**
                 * Insert widget to the container.
                 * @param widget
                 * @param at
                 * @param options
                 */
                Container.prototype.insert = function (widget, at, options) {
                    if (options === void 0) { options = null; }
                    var at_index;
                    var class_id;
                    var pushable;
                    var cell = null;
                    if (!(widget instanceof ui.Widget)) {
                        throw new Error('Instance of widget is required!');
                    }
                    // UID only, no options
                    if (!options) {
                        options = { uid: widget.uid };
                    }
                    else if (typeof options === 'string') {
                        options = { uid: options };
                    }
                    else if (typeof options === 'object') {
                        if (typeof options.uid === 'undefined') {
                            options.uid = widget.uid;
                        }
                    }
                    else {
                        throw new Error('Invalid options provided. Null, string or {} allowed.');
                    }
                    // Create classes
                    class_id = 'coll-euid-' + widget.uid + ' coll-uid-' + options.uid;
                    // Create wrapper, append at the end of the list
                    if (this.element_wrapper) {
                        pushable = $(this.element_wrapper);
                        pushable.addClass(class_id);
                        if (pushable.filter('.container-target').length) {
                            pushable.filter('.container-target').append(widget.element);
                        }
                        else if (pushable.find('.container-target').length) {
                            pushable.find('.container-target').append(widget.element);
                        }
                        else {
                            throw new Error("Cannot find .container-target!");
                        }
                        cell = new this.Cell_constructor(this, pushable, options);
                    }
                    else {
                        widget.element.addClass(class_id);
                        pushable = widget.element;
                    }
                    // Either push after another element or at the end of the list
                    if (at > -1) {
                        at_index = this.collection.push_after(at, options.uid, [widget, cell]);
                    }
                    else {
                        at_index = this.collection.push(options.uid, [widget, cell]);
                    }
                    // Either inster after particular element or just at the end
                    if (at > -1) {
                        this.$target
                            .find('.coll-euid-' + this.collection.get_from(at_index, -1).uid)
                            .after(pushable);
                    }
                    else {
                        this.$target.append(pushable);
                    }
                    return widget;
                };
                /**
                * Get elements from the collection. If `cell` is provided, get cell itself.
                * @param uid  either string (uid) or number (index)
                * You can chain IDs to get to the last, by using: id1 > id2 > id3
                * All elements in chain must be of type Container for this to work.
                * @param cell weather to get cell itself rather than containing element.
                */
                Container.prototype.get = function (uid, cell) {
                    // Used in chain
                    var index_at;
                    // Deal with a chained uid
                    // Get uid of first segment in a chain, example: uid > uid2 > uid3  
                    if (typeof uid === 'string' && (index_at = uid.indexOf('>')) > -1) {
                        var uidq = uid.substr(0, index_at).trim();
                        var ccontainer = this.collection.get(uidq)[0];
                        if (ccontainer instanceof Container) {
                            return ccontainer.get(uid.substr(index_at + 1).trim(), cell);
                        }
                        else {
                            throw new Error("Failed to acquire an element. Container needed: " + uidq);
                        }
                    }
                    if (cell) {
                        return this.collection.get(uid)[1];
                    }
                    else {
                        return this.collection.get(uid)[0];
                    }
                };
                /**
                 * Remove particular cell (and the containing element)
                 * @param uid
                 */
                Container.prototype.remove = function (uid) {
                    uid = this.collection.get(uid).uid;
                    this.collection.remove(uid);
                    this.$target.find('.coll-euid-' + uid).remove();
                };
                return Container;
            })(ui.Widget);
            ui.Container = Container;
        })(ui = js.ui || (js.ui = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
