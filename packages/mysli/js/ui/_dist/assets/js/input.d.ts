/// <reference path="widget.d.ts" />
/// <reference path="_inc.common.d.ts" />
declare module mysli.js.ui {
    class Input extends Widget {
        protected static template: string;
        protected $input: JQuery;
        static TYPE_TEXT: string;
        static TYPE_PASSWORD: string;
        constructor(options?: any);
        type: string;
        placeholder: string;
        label: string;
    }
}
