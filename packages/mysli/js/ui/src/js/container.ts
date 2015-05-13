/// <reference path="widget.ts" />
/// <reference path="cell.ts" />
/// <reference path="_inc.common.ts" />
module mysli.js.ui {
    export class Container extends Widget {

        // Allows to replace cell interface when extending this class
        protected Cell_constructor: any = Cell;

        // Collection of all contained elements
        protected collection: common.Arr = new common.Arr();

        // Where the contained elements will be placed
        protected $target: JQuery;

        protected element_wrapper: string = '<div class="ui-cell container-target"></div>';

        constructor(options: any = {}) {
            super(options);

            this.element.addClass('ui-container');
            this.$target = this.element;
        }

        /**
         * Push widget to the contaner
         * @param widget
         * @param options
         */
        push(widgets: Widget|Widget[], options: any = null): Widget|Widget[] {
            return this.insert(widgets, -1, options);
        }

        /**
         * Insert widget to the container.
         * @param widget
         * @param at
         * @param options
         */
        insert(widgets: Widget|Widget[], at: number, options?: any): Widget|Widget[] {
            var at_index: number;
            var class_id: string;
            var pushable: JQuery;
            var widget: Widget;
            var cell: Cell = null;

            if (!(widgets instanceof Widget)) {
                if (widgets.constructor === Array) {
                    for (var i = 0; i < (<Widget[]> widgets).length; i++) {
                        this.insert(widgets[i], at, options);
                    }
                    return widgets;
                } else {
                    throw new Error('Instance of widget|widgets[] is required!');
                }
            } else {
                widget = widgets;
            }

            // UID only, no options
            if (!options) {
                options = {uid: widget.uid}
            } else if (typeof options === 'string') {
                options = {uid: options};
            } else if (typeof options === 'object') {
                if (typeof options.uid === 'undefined') {
                    options.uid = widget.uid;
                }
            } else {
                throw new Error('Invalid options provided. Null, string or {} allowed.');
            }

            if (this.collection.has(options.uid)) {
                throw new Error(`Element with such ID already exists: ${options.id}`);
            }

            // Create classes
            class_id = 'coll-euid-'+widget.uid+' coll-uid-'+options.uid;

            // Create wrapper, append at the end of the list
            if (this.element_wrapper) {
                pushable = $(this.element_wrapper);
                pushable.addClass(class_id);
                if (pushable.filter('.container-target').length) {
                    pushable.filter('.container-target').append(widget.element);
                } else if (pushable.find('.container-target').length) {
                    pushable.find('.container-target').append(widget.element);
                } else {
                    throw new Error("Cannot find .container-target!");
                }

                cell = new this.Cell_constructor(this, pushable, options);
            } else {
                widget.element.addClass(class_id);
                pushable = widget.element;
            }

            // Either push after another element or at the end of the list
            if (at > -1) {
                at_index = this.collection.push_after(at, options.uid, [widget, cell]);
            } else {
                at_index = this.collection.push(options.uid, [widget, cell]);
            }

            // Either inster after particular element or just at the end
            if (at > -1) {
                this.$target
                    .find('.coll-euid-'+this.collection.get_from(at_index, -1).uid)
                        .after(pushable);
            } else {
                this.$target.append(pushable);
            }

            return widget;
        }

         /**
         * Get elements from the collection. If `cell` is provided, get cell itself.
         * @param uid  either string (uid) or number (index)
         * You can chain IDs to get to the last, by using: id1 > id2 > id3
         * All elements in chain must be of type Container for this to work.
         * @param cell weather to get cell itself rather than containing element.
         */
        get(uid: string|number, cell: boolean = false): Cell|Widget {
            // Used in chain
            var index_at: number;

            // Deal with a chained uid
            // Get uid of first segment in a chain, example: uid > uid2 > uid3
            if (typeof uid === 'string' && (index_at = uid.indexOf('>')) > -1) {
                var uidq: string = uid.substr(0, index_at).trim();
                var ccontainer: any = this.collection.get(uidq)[0];

                if (ccontainer instanceof Container) {
                    return ccontainer.get(uid.substr(index_at+1).trim(), cell);
                } else {
                    throw new Error(`Failed to acquire an element. Container needed: ${uidq}`);
                }
            }

            if (cell) {
                return this.collection.get(uid)[1];
            } else {
                return this.collection.get(uid)[0];
            }
        }

        /**
         * Get an element, andthen remove it from the collction and DOM.
         * @param uid
         */
        pull(uid: string|number): Widget {
            var element: Widget = <Widget> this.get(uid, false);
            this.remove(uid);
            return element;
        }

        /**
         * Check if uid is in the collection.
         * @param uid
         */
        has(uid: string|number): boolean {
            return this.collection.has(uid);
        }

        /**
         * Remove particular cell (and the containing element)
         * @param uid
         */
        remove(uid: string|number) {
            uid = this.collection.get(uid).uid;
            this.collection.remove(uid);
            this.$target.find('.coll-euid-'+uid).remove();
        }
    }
}