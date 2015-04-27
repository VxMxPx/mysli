/// <reference path="container.d.ts" />
/// <reference path="_inc.common.d.ts" />
declare module mysli.js.ui {
    class Cell {
        protected parent: Container;
        protected $cell: JQuery;
        protected prop: any;
        constructor(parent: Container, $cell: JQuery, options?: any);
        /**
         * Animate the cell.
         * @param what
         * @param duration
         * @param callback
         */
        animate(what: any, duration?: number, callback?: any): void;
        visible: boolean;
        /**
         * Remove cell from a collection.
         */
        remove(): void;
    }
}
