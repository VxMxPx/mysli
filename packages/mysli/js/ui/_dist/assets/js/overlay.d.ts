/// <reference path="widget.d.ts" />
declare module mysli.js.ui {
    class Overlay extends Widget {
        protected static template: string;
        constructor(options: any);
        busy: boolean;
        visibility: boolean;
    }
}
