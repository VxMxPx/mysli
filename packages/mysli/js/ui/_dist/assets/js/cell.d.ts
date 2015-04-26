/// <reference path="container.d.ts" />
/// <reference path="_inc.common.d.ts" />
declare module mysli.js.ui {
    class Cell {
        private parent;
        private $cell;
        private prop;
        constructor(parent: Container, $cell: JQuery);
        /**
         * Animate the cell.
         * @param {any}    what
         * @param {number} duration
         * @param {any}    callback
         */
        animate(what: any, duration?: number, callback?: any): void;
        /**
         * Change cell visibility
         * @param  {boolean}  status
         * @return {boolean}
         */
        visible(status?: boolean): boolean;
        /**
         * Remove cell from a collection.
         */
        remove(): void;
    }
}
