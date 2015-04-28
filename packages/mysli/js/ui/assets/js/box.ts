/// <reference path="container.ts" />
/// <reference path="cell.ts" />
/// <reference path="_inc.common.ts" />
module mysli.js.ui {
    export class Box extends Container {

        protected Cell_constructor: any = BoxCell;

        protected element_wrapper: string;
        private element_wrapper_original: string;

        public static get HORIZONTAL(): number { return 1; }
        public static get VERTICAL(): number { return 2; }

        constructor(options: any = {}) {
            super(options);

            this.prop.def({
               orientation: Box.HORIZONTAL
            });
            this.prop.push(options);

            this.element.addClass('ui-box');
            this.element_wrapper_original = this.element_wrapper;

            if (this.prop.orientation === Box.VERTICAL) {
                var row:JQuery = $('<div class="ui-row" />');
                this.element.append(row);
                this.$target = row;
            }
        }

        /**
         * Override insert, to support horizontal/vertical layout.
         */
        insert(...args): Widget {
            if (this.prop.orientation === Box.HORIZONTAL) {
                this.element_wrapper = '<div class="ui-row"><div class="ui-cell container-target" /></div>';
            } else {
                this.element_wrapper = this.element_wrapper_original;
            }
            return super.insert.apply(this, args);
        }
    }

    class BoxCell extends Cell {
        constructor(parent: Container, $cell: JQuery, options: any = {}) {
            super(parent, $cell, options);
            this.prop.def({expanded: false});
            this.prop.push(options, ['expanded']);
        }

        // Get/set expanded
        get expanded(): boolean {
            return this.prop.expanded;
        }
        set expanded(value: boolean) {
            this.$cell[value ? 'addClass' : 'removeClass']('expanded');
        }
    }
}
