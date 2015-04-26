/// <reference path="widget.d.ts" />
/// <reference path="_inc.common.d.ts" />
declare module mysli.js.ui {
    class HTML extends Widget {
        constructor(options: any);
        /**
         * Push new HTML to the container.
         * @param  {string} html
         * @param  {string} uid
         * @return {JQuery}
         */
        push(html: string, uid?: string): JQuery;
        /**
         * Remove element(s) by specific jQuery selector.
         * @param {string} selector
         */
        remove(selector: string): void;
    }
}
