module mysli.js.ui {
    export class Util {
        static mix(defaults, options={}) {
            return $.extend({}, defaults, options);
        }
        /**
         * Call object's method for each option defined in 'methods'.
         * @param  {object} options
         * @param  {object} context
         * @param  {object} methods
         */
        static use(options, context, methods={}) {
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
}
