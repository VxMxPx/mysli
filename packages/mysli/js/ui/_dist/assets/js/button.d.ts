/// <reference path="widget.d.ts" />
/// <reference path="_inc.common.d.ts" />
declare module mysli.js.ui {
    class Button extends Widget {
        protected static template: string;
        constructor(options?: any);
        /**
          * Get/Set button's label.
          * @param  {string} value
          * @return {string}
          */
        label(value?: string): string;
        /**
         * Get/Set Button's Icon
         * @param  {any} String: icon || Object: {icon: 0, position: 0, spin: 0} || false: remove icon
         * @return {any} {icon: 0, position: 0, spin: 0}
         */
        icon(icon?: any): any;
    }
}
