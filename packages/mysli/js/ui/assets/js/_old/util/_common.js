mysli.js.ui.util = (function() {
    'use strict';

    return {
        /**
         * Call object's method for each option defined in 'methods'.
         * @param  {Object} options
         * @param  {Object} context
         * @param  {Object} methods
         */
        use: function (options, context, methods) {
            var arg, call, expect, params, _i, _len;

            if (typeof options !== 'object') {
                return;
            }

            if (methods === null) {
                methods = {};
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
    };

}());
