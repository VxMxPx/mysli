/// <reference path="../../../assets/js/_jquery.d.ts" />
declare module mysli.js.common {
    function mix(defaults: any, options?: any): any;
    /**
     * Call object's method for each option defined in 'methods'.
     * @param  {object} options
     * @param  {object} context
     * @param  {object} methods
     */
    function use(options: any, context: any, methods?: {}): void;
}
