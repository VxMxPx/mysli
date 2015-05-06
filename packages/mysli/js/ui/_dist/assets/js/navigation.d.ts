/// <reference path="button.d.ts" />
/// <reference path="box.d.ts" />
/// <reference path="widget.d.ts" />
/// <reference path="_inc.common.d.ts" />
declare module mysli.js.ui {
    class Navigation extends Widget {
        protected container: Box;
        constructor(items: any, options?: any);
        private produce(title, id);
    }
}
