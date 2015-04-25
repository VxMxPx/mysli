var mysli;
(function (mysli) {
    var js;
    (function (js) {
        var ui;
        (function (ui) {
            var Util = (function () {
                function Util() {
                }
                Util.mix = function (defaults, options) {
                    if (options === void 0) { options = {}; }
                    return $.extend({}, defaults, options);
                };
                /**
                 * Call object's method for each option defined in 'methods'.
                 * @param  {object} options
                 * @param  {object} context
                 * @param  {object} methods
                 */
                Util.use = function (options, context, methods) {
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
                };
                return Util;
            })();
            ui.Util = Util;
        })(ui = js.ui || (js.ui = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
