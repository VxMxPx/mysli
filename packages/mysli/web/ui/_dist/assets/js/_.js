mysli.web.ui._ = (function() {

    'use strict';

    var uidc = 0;

    return {

        // Placeholder for extend
        extend: {},

        /**
         * Get new unique ID for an object.
         * @param  {Object} element
         * @return {string}
         */
        uid: function (element) {
            var uuid = "muid-"+(++uidc);
            element.addClass(uuid);
            return uuid;
        },

        /**
         * Call object's method for each option defined in 'methods'.
         * @param  {Object} options
         * @param  {Object} context
         * @param  {Object} methods
         */
        use: function (options, context, methods) {
            var arg, call, expect, params, _i, _len;

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
        },

        /**
         * Merge options with defaults
         * @param  {Object} defaults
         * @param  {Object} options
         * @return {Object}
         */
        merge: function (defaults, options) {
            return $.extend({}, defaults, options);
        },

        /**
         * Format number
         * @param  {Integer} number
         * @param  {Integer} decimals
         * @param  {String}  dec_point
         * @param  {String}  thousands_sep
         * @return {String}
         */
        number_format: function(number, decimals, dec_point, thousands_sep) {
            var n, prec, s, to_fixed_fix;

            if (dec_point === null) {
                dec_point = '.';
            }

            if (thousands_sep === null) {
                thousands_sep = ',';
            }

            number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
            n = isFinite(+number) ? +number : 0;
            prec = isFinite(+decimals) ? Math.abs(decimals) : 0;
            s = '';

            to_fixed_fix = function(n, prec) {
                var k;
                k = Math.pow(10, prec);
                return '' + Math.round(n * k) / k;
            };

            s = prec ? to_fixed_fix(n, prec) : '' + Math.round(n);
            s = s.split('.');

            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, thousands_sep);
            }

            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }

            return s.join(dec_point);
        },

        /**
         * Get X% by Y of Z.
         * @param  {Integer} amount
         * @param  {Integer} total
         * @param  {Integer} percision
         * @return {Integer}
         */
        get_percent: function(amount, total, percision) {
            var count;

            if (typeof amount === 'number' && typeof total === 'number') {
                percision = percision || 2;

                if (!amount || !total) {
                    return amount;
                }

                count = amount / total;
                count = count * 100;
                count = parseFloat(this.number_format(count, percision));

                return count;
            }

            return false;
        },

        /**
         * Get X by Y% of Z
         * @param {Integer} percent
         * @param {Integer} total
         * @param {Integer} percision
         */
        set_percent: function(percent, total, percision) {
            var result;

            if (typeof percent === 'number' && typeof total === 'number') {
                percision = percision || 2;

                if (!percent || !total) {
                    return 0;
                }

                result = parseFloat(this.number_format((total / 100) * percent, percision));

                return result;
            }

            return false;
        }
    };

}());
