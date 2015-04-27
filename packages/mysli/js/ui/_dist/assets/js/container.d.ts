/// <reference path="widget.d.ts" />
/// <reference path="cell.d.ts" />
/// <reference path="_inc.common.d.ts" />
declare module mysli.js.ui {
    class Container extends Widget {
        protected Cell_constructor: any;
        protected collection: common.Arr;
        protected $target: JQuery;
        protected element_wrapper: string;
        constructor(options?: any);
        /**
         * Push widget to the contaner
         * @param widget
         * @param options
         */
        push(widget: Widget, options?: any): Widget;
        /**
         * Insert widget to the container.
         * @param widget
         * @param at
         * @param options
         */
        insert(widget: Widget, at: number, options?: any): Widget;
        /**
        * Get elements from the collection. If `cell` is provided, get cell itself.
        * @param uid  either string (uid) or number (index)
        * @param cell weather to get cell itself rather than containing element.
        */
        get(uid: string | number, cell: boolean): Cell | Widget;
        /**
         * Remove particular cell (and the containing element)
         * @param uid
         */
        remove(uid: string | number): void;
    }
}
