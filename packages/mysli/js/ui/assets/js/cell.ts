/// <reference path="container.ts" />
/// <reference path="_jquery.d.ts" />
module mysli.js.ui {
    export class Cell {

        private parent:Container;
        private $cell:JQuery;
        private prop:any = {};

        constructor(parent:Container, $cell:JQuery) {
            this.parent = parent;
            this.$cell = $cell;

            this.prop.visible = true;
        }

        /**
         * Animate the cell.
         * @param {any}    what
         * @param {number} duration
         * @param {any}    callback
         */
        animate(what:any, duration:number=500, callback:any=false):void {
            this.$cell.animate(what, duration, callback);
        }

        /**
         * Change cell visibility
         * @param  {boolean}  status
         * @return {boolean}
         */
        visible(status?:boolean):boolean {
            if (typeof status !== 'undefined' && status !== this.prop.visible) {
                this.prop.visible = status;
                if (status) {
                    this.$cell.show();
                } else {
                    this.$cell.hide();
                }
            }
            return this.prop.visible;
        }

        /**
         * Remove cell from a collection.
         */
        remove():void {
            this.$cell.remove();
        }
    }
}
