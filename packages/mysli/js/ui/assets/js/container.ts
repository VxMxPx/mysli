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
        push(widget: Widget, options: any = null): Widget {
            return this.insert(widget, -1, options);
        }

        /**
         * Insert widget to the container.
         * @param widget
         * @param at
         * @param options
         */
        insert(widget: Widget, at: number, options: any = null): Widget {
            var at_index: number;
            var class_id: string;
            var pushable: JQuery;
            var cell: Cell = null;

            if (!(widget instanceof Widget)) {
                throw new Error('Instance of widget is required!');
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
         * @param cell weather to get cell itself rather than containing element.
         */
        get(uid: string|number, cell: boolean): Cell|Widget {
            if (cell) {
                return this.collection.get(uid)[1];
            } else {
                return this.collection.get(uid)[0];
            }
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
