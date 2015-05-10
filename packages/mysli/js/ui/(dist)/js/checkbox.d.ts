/// <reference path="generic_input.d.ts" />
/// <reference path="_inc.common.d.ts" />
declare module mysli.js.ui {
    class Checkbox extends GenericInput {
        protected static template: string;
        protected $checked: JQuery;
        constructor(options?: any);
        checked: boolean;
    }
}
