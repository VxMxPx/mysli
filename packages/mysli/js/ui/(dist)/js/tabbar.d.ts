/// <reference path="widget.d.ts" />
/// <reference path="box.d.ts" />
/// <reference path="button.d.ts" />
/// <reference path="_inc.common.d.ts" />
declare module mysli.js.ui {
    class Tabbar extends Widget {
        protected container: Box;
        constructor(items: any, options?: any);
        active: string;
        private produce(title, id);
    }
}
