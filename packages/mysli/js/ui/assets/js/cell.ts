/// <reference path="container.ts" />
/// <reference path="_inc.common.ts" />
module mysli.js.ui {
    export class Cell {

        protected parent: Container;
        protected $cell: JQuery;
        protected prop: any;

        constructor(parent: Container, $cell: JQuery, options: any = {}) {
            this.parent = parent;
            this.$cell = $cell;

            // Set default values
            this.prop = {
                visible: true
            };

            // Apply new values if needed
            this.visible = options.visible || true;
        }

        /**
         * Animate the cell.
         * @param what
         * @param duration
         * @param callback
         */
        animate(what: any, duration: number = 500, callback: any = false): void {
            this.$cell.animate(what, duration, callback);
        }

        // Get/set visibility
        get visible(): boolean {
            return this.prop.visible;
        }
        set visible(status: boolean) {
            if (status === this.prop.visible) { return; }

            this.prop.visible = status;
            this.$cell[status ? 'show' : 'hide']();
        }

        /**
         * Remove cell from a collection.
         */
        remove():void {
            this.$cell.remove();
        }
    }
}
