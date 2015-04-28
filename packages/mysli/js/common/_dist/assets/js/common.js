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
        })(common = js.common || (js.common = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
