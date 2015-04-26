/// <reference path="widget.d.ts" />
/// <reference path="_inc.common.d.ts" />
declare module mysli.js.ui {
    class Navigation extends Widget {
        protected static allowed_styles: string[];
        private static collection;
        constructor(options: {
            items: any;
        });
    }
}
