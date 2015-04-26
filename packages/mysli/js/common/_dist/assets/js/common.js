/// <reference path="_jquery.d.ts" />
var mysli;
(function (mysli) {
    var js;
    (function (js) {
        var common;
        (function (common) {
            function mix(defaults, options) {
                if (options === void 0) { options = {}; }
                return $.extend({}, defaults, options);
            }
            common.mix = mix;
            /**
             * Call object's method for each option defined in 'methods'.
             * @param  {object} options
             * @param  {object} context
             * @param  {object} methods
             */
            function use(options, context, methods) {
                if (methods === void 0) { methods = {}; }
                var arg, call, expect, params, _i, _len;
                if (typeof options !== 'object') {
                    return;
                }
                for (call in methods) {
                    expect = methods[call];
                    params = [];
                    if (typeof expect === 'string') {
                        params.push(options[expect]);
                    }
                    else {
                        for (_i = 0, _len = expect.length; _i < _len; _i++) {
                            arg = expect[_i];
                            params.push(options[arg]);
                        }
                    }
                    context[call].apply(context, params);
                }
            }
            common.use = use;
        })(common = js.common || (js.common = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
