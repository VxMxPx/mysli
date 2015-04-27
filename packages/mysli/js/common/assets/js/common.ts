/// <reference path="_jquery.d.ts" />
module mysli.js.common {

    export function mix(defaults: any, options: any = {}): any {
        return $.extend({}, defaults, options);
    }

    /**
     * Call object's method for each option defined in 'methods'.
     * @param options
     * @param context
     * @param methods
     */
    export function use(options: any, context: any, methods: any = {}): any {

        var arg: string;
        var call: string;
        var expect: any;
        var params: any[];
        var _i: number;
        var _len: number;

        if (typeof options !== 'object') {
            return;
        }

        for (call in methods) {
            if (!methods.hasOwnProperty(call)) {
                continue;
            }
            expect = methods[call];
            params = [];

            if (typeof expect === 'string') {
                params.push(options[expect]);
            } else {
                for (_i = 0, _len = expect.length; _i < _len; _i++) {
                    arg = expect[_i];
                    params.push(options[arg]);
                }
            }
            context[call].apply(context, params);
        }
    }
}
