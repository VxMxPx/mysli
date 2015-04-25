mysli.js.common = (function ($) {

    'use strict';

    if (typeof $ === 'undefined') {
        throw new Error('mysli.js.common requires jQuery.');
    }

    return {

        /**
         * Merge options with defaults
         * @param  {Object} defaults
         * @param  {Object} options
         * @return {Object}
         */
        merge: function (defaults, options) {
            if (typeof options !== 'object') {
                options = {};
            }
            return $.extend({}, defaults, options);
        },

        /**
         * Whether the object has the specified property.
         * @param {object} ... object to test
         * @param {string} ... property name
         * @return {boolean}
         */
        has_own_property: {}.hasOwnProperty,

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

}(jQuery));
