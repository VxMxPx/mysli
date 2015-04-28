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

            this.prop = new common.Prop({
                visible: true,
                padding: false
            }, this);
            this.prop.push(options, ['visible', 'padding']);
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

        // Get/set padded
        get padding(): boolean|any[] {
            return this.prop.padding;
        }
        set padding(value: boolean|any[]) {
            var positions: string[] = ['top', 'right', 'bottom', 'left'];

            this.$cell.css('padding', '');

            if (typeof value === 'boolean') {
                value = [value, value, value, value];
            }

            for (var i=0; i<positions.length; i++) {
                if (typeof value[i] === 'number') {
                    this.$cell.css(`padding-${positions[i]}`, value[i]);
                } else {
                    this.$cell[value[i] ? 'addClass' : 'removeClass'](`pad${positions[i]}`);
                }
            }
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
