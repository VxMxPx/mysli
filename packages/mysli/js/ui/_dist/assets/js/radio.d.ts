/// <reference path="widget.d.ts" />
/// <reference path="box.d.ts" />
/// <reference path="generic_input.d.ts" />
/// <reference path="_inc.common.d.ts" />
declare module mysli.js.ui {
    class Radio extends GenericInput {
        protected static template: string;
        protected $checked: JQuery;
        constructor(options?: any);
        checked: boolean;
        toggle: boolean;
    }
    class RadioGroup extends Widget {
        protected box: Box;
        protected checked: Radio;
        constructor(elements: any[], options?: any);
    }
}
