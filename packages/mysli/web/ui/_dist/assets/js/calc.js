;(function (MU) {

    'use strict';

    MU.Calc = {

        numberFormat : function (number, decimals, dec_point, thousands_sep) {
            // Strip all characters but numerical ones.
            number = (number + '').replace(/[^0-9+\-Ee.]/g, '');

            var n    = !isFinite(+number)   ? 0 : +number,
                prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                sep  = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                dec  = (typeof dec_point === 'undefined')     ? '.' : dec_point,
                s    = '',
                toFixedFix = function (n, prec) {
                    var k = Math.pow(10, prec);
                    return '' + Math.round(n * k) / k;
                };

            // Fix for IE parseFloat(0.55).toFixed(0) = 0;
            s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');

            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }

            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }

            return s.join(dec);
        },

        getPercent : function (amount, total, percision) {
            var count;

            if (typeof amount === 'number' && typeof total === 'number') {
                percision = percision || 2;
                if (!amount || !total) {
                    return amount;
                }
                count = amount / total;
                count = count * 100;
                count = parseFloat(this.numberFormat(count, percision));
                return count;
            }

            return false;
        },

        setPercent : function (percent, total, percision) {
            if (typeof percent === 'number' && typeof total === 'number') {

                percision = percision || 2;

                if (!percent || !total) {
                    return 0;
                }

                // Calculate percent from total
                return parseFloat(this.numberFormat((total / 100) * percent, percision));
            }

            return false;
        }
    };

}(Mysli.UI));
