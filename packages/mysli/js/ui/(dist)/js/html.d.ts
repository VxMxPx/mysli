/// <reference path="widget.d.ts" />
/// <reference path="_inc.common.d.ts" />
declare module mysli.js.ui {
    class HTML extends Widget {
        constructor(text?: any, options?: any);
        /**
         * Push new HTML to the container.
         * @param html
         */
        push(html: string): JQuery;
        /**
         * Replace all content in the container, with a new content...
         */
        replace(html: string): JQuery;
        /**
         * Remove element(s) by specific jQuery selector.
         * @param selector
         */
        remove(selector: string): void;
    }
}
