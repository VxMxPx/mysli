/// <reference path="widget.d.ts" />
/// <reference path="_inc.common.d.ts" />
declare module mysli.js.ui {
    class Label extends Widget {
        static DEFAULT: number;
        static TITLE: number;
        static INPUT: number;
        protected static template: string;
        constructor(options?: any);
        /**
         * Get/set type.
         */
        type: number;
        /**
         * Get/set text
         */
        text: string;
        /**
         * Connect an input to a wiget.
         */
        input: Widget;
    }
}
