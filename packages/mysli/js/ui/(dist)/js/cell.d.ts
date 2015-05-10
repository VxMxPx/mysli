/// <reference path="container.d.ts" />
/// <reference path="_inc.common.d.ts" />
declare module mysli.js.ui {
    class Cell {
        protected parent: Container;
        protected $cell: JQuery;
        protected prop: any;
        static SCROLL_Y: string;
        static SCROLL_X: string;
        static SCROLL_BOTH: string;
        static SCROLL_NONE: string;
        constructor(parent: Container, $cell: JQuery, options?: any);
        /**
         * Animate the cell.
         * @param what
         * @param duration
         * @param callback
         */
        animate(what: any, duration?: number, callback?: any): void;
        padding: boolean | any[];
        visible: boolean;
        scroll: string;
        /**
         * Remove cell from a collection.
         */
        remove(): void;
    }
}
