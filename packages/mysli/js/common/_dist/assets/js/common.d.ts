/// <reference path="../../../assets/js/_jquery.d.ts" />
declare module mysli.js.common {
    function mix(defaults: any, options?: any): any;
    /**
     * Call object's method for each option defined in 'methods'.
     * @param options
     * @param context
     * @param methods
     */
    function use(options: any, context: any, methods?: any): any;
}
