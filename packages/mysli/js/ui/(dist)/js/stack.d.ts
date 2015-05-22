/// <reference path="container.d.ts" />
/// <reference path="_inc.common.d.ts" />
declare module mysli.js.ui {
    class Stack extends Container {
        private current;
        static ANI_SLIDE_UP: string;
        static ANI_SLIDE_DOWN: string;
        static ANI_SLIDE_LEFT: string;
        static ANI_SLIDE_RIGHT: string;
        static ANI_FADE: string;
        constructor(options?: any);
        animation: string;
        /**
         * Go to particular view.
         * @param id
         */
        to(id: string): void;
        /**
         * Animate cell(s)
         * @param cell
         * @param type
         */
        private animate(cell, type);
    }
}
