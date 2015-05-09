/// <reference path="generic_input.d.ts" />
/// <reference path="_inc.common.d.ts" />
declare module mysli.js.ui {
    class Entry extends GenericInput {
        protected static template: string;
        static TYPE_TEXT: string;
        static TYPE_PASSWORD: string;
        constructor(options?: any);
        type: string;
        placeholder: string;
    }
}
