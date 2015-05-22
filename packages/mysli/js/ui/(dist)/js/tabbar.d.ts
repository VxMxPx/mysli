/// <reference path="widget.d.ts" />
/// <reference path="box.d.ts" />
/// <reference path="button.d.ts" />
/// <reference path="stack.d.ts" />
/// <reference path="_inc.common.d.ts" />
declare module mysli.js.ui {
    class Tabbar extends Widget {
        protected container: Box;
        constructor(options?: any);
        stack: Stack;
        active: string;
        /**
         * Add items to the container
         * @param items in format: {id: Label, id: Label}
         */
        add(items: any): void;
        /**
         * Produce a new tab element.
         * @param title
         * @param id
         */
        private produce(title, id);
    }
}
