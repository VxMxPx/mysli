/// <reference path="widget.d.ts" />
/// <reference path="_inc.common.d.ts" />
declare module mysli.js.ui {
    class Button extends Widget {
        protected static template: string;
        protected static allowed_styles: string[];
        constructor(options?: any);
        label: string;
        icon: string | {
            name?: string;
            position?: string;
            spin?: boolean;
        };
    }
}
