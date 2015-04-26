/// <reference path="widget.d.ts" />
/// <reference path="cell.d.ts" />
/// <reference path="_inc.common.d.ts" />
declare module mysli.js.ui {
    class Container extends Widget {
        protected collection: common.Arr;
        protected $target: JQuery;
        protected static element_wrapper: string;
        constructor(options?: {});
        /**
         * Push widget to the contaner
         * @param  {Widget} element
         * @param  {string} uid
         * @return {Widget}
         */
        push(widget: Widget, uid?: string): Widget;
        /**
         * Insert widget to the container.
         * @param  {Widget} widget
         * @param  {number} at
         * @param  {string} uid
         * @return {Widget}
         */
        insert(widget: Widget, at: number, uid?: string): Widget;
        /**
        * Get elements from the collection. If `cell` is provided, get cell itself.
        * @param  {string|number} uid  either string (uid) or number (index)
        * @param  {boolean}       cell weather to get cell itself rather than containing element.
        * @return {any}
        */
        get(uid: string | number, cell: boolean): any;
        /**
         * Remove particular cell (and the containing element)
         * @param {string|number} uid
         */
        remove(uid: string | number): void;
    }
}
