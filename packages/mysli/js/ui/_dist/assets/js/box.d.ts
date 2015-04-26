/// <reference path="container.d.ts" />
/// <reference path="_inc.common.d.ts" />
declare module mysli.js.ui {
    class Box extends Container {
        protected static element_wrapper: string;
        private element_wrapper_original;
        static HORIZONTAL: number;
        static VERTICAL: number;
        constructor(options?: {});
        /**
         * Override get, to support expanded method.
         */
        get(): any;
        /**
         * Override insert, to support horizontal/vertical layout.
         */
        insert(): Widget;
        /**
         * Method to be appended to the Cell object.
         * @param  {boolean} status
         * @return {boolean}
         */
        private static expanded(status?);
    }
}
