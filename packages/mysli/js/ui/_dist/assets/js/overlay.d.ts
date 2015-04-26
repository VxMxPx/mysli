/// <reference path="widget.d.ts" />
declare module mysli.js.ui {
    class Overlay extends Widget {
        protected static template: string;
        constructor(options: any);
        /**
         * Get/Set busy state.
         * @param  {boolean} status
         * @return {boolean}
         */
        busy(status?: boolean): boolean;
        /**
         * Get/Set visibility state.
         * @param  {boolean} status
         * @return {boolean}
         */
        visible(status?: boolean): boolean;
    }
}
