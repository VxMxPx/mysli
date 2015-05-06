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
        push(widgets: Widget | Widget[], options?: any): Widget | Widget[];
        /**
         * Insert widget to the container.
         * @param widget
         * @param at
         * @param options
         */
        insert(widgets: Widget | Widget[], at: number, options?: any): Widget | Widget[];
        /**
        * Get elements from the collection. If `cell` is provided, get cell itself.
        * @param uid  either string (uid) or number (index)
        * You can chain IDs to get to the last, by using: id1 > id2 > id3
        * All elements in chain must be of type Container for this to work.
        * @param cell weather to get cell itself rather than containing element.
        */
        get(uid: string | number, cell?: boolean): Cell | Widget;
        /**
         * Get an element, andthen remove it from the collction and DOM.
         * @param uid
         */
        pull(uid: string | number): Widget;
        /**
         * Check if uid is in the collection.
         * @param uid
         */
        has(uid: string | number): boolean;
        /**
         * Remove particular cell (and the containing element)
         * @param uid
         */
        remove(uid: string | number): void;
    }
}
