/// <reference path="container.d.ts" />
/// <reference path="cell.d.ts" />
/// <reference path="_inc.common.d.ts" />
declare module mysli.js.ui {
    class Box extends Container {
        protected element_wrapper: string;
        private element_wrapper_original;
        static HORIZONTAL: number;
        static VERTICAL: number;
        constructor(options?: any);
        /**
         * Override insert, to support horizontal/vertical layout.
         */
        insert(...args: any[]): Widget | Widget[];
    }
}
