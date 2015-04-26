/// <reference path="_jquery.d.ts" />
module mysli.js.common {

    export function mix(defaults: any, options: any={}):any {
        return $.extend({}, defaults, options);
    }

    /**
     * Call object's method for each option defined in 'methods'.
     * @param  {object} options
     * @param  {object} context
     * @param  {object} methods
     */
    export function use(options, context, methods={}) {

        var arg, call, expect, params, _i, _len;

        if (typeof options !== 'object') {
            return;
        }

        for (call in methods) {
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
