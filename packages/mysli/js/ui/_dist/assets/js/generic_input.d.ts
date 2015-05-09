/// <reference path="widget.d.ts" />
/// <reference path="_inc.common.d.ts" />
declare module mysli.js.ui {
    class GenericInput extends Widget {
        protected static element: string;
        protected $input: JQuery;
        protected $label: JQuery;
        constructor(options?: any);
        disabled: boolean;
        label: string;
    }
}
