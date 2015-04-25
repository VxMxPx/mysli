/// <reference path="container.ts" />
/// <reference path="_jquery.d.ts" />
module mysli.js.ui {
    export class Box extends Container {

        protected static element_wrapper: string;
        private element_wrapper_original: string;

        constructor(options={}) {
            super(options);

            // Apply own defaults first...
            this.prop = Util.mix({
                orientation: 'horizontal'
            }, this.prop);

            this.element().addClass('ui-box');

            Box.element_wrapper = Container.element_wrapper;

            if (this.prop.orientation === 'vertical') {
                var row:JQuery = $('<div class="ui-row" />');
                this.element().append(row);
                this.$target = row;
            }
        }

        /**
         * Override get, to support expanded method.
         */
        get():any {
            var result:any = super.get.apply(this, arguments);
            if (result instanceof Cell) {
                result.expanded = Box.expanded.bind(result);
            }
            return result;
        }

        /**
         * Override insert, to support horizontal/vertical layout.
         */
        insert():Widget {
            if (this.prop.orientation === 'horizontal') {
                Box.element_wrapper = '<div class="ui-row"><div class="ui-cell container-target" /></div>';
            } else {
                Box.element_wrapper = this.element_wrapper_original;
            }
            return super.insert.apply(this, arguments);
        }

        /**
         * Method to be appended to the Cell object.
         * @param  {boolean} status
         * @return {boolean}
         */
        private static expanded(status?:boolean):boolean {
            if (typeof status !== 'undefined') {
                this.$cell[status ? 'addClass' : 'removeClass']('cell-expanded');
            }
            return this.$cell.hasClass('cell-expanded');
        }
    }
}
