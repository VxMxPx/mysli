var mysli;
(function (mysli) {
    var js;
    (function (js) {
        var common;
        (function (common) {
            var Num = (function () {
                function Num() {
                }
                Num.to_fixed_fix = function (n, prec) {
                    var k;
                    k = Math.pow(10, prec);
                    return '' + Math.round(n * k) / k;
                };
                /**
                 * Format number
                 * @param  {number} number
                 * @param  {number} decimals
                 * @param  {string} dec_point
                 * @param  {string} thousands_sep
                 * @return {string}
                 */
                Num.format = function (number, decimals, dec_point, thousands_sep) {
                    if (dec_point === void 0) { dec_point = '.'; }
                    if (thousands_sep === void 0) { thousands_sep = ','; }
                    var s_num;
                    var o_num;
                    var prec;
                    var final;
                    var final_seg;
                    s_num = (number + '').replace(/[^0-9+\-Ee.]/g, '');
                    o_num = isFinite(+s_num) ? +s_num : 0;
                    prec = isFinite(+decimals) ? Math.abs(decimals) : 0;
                    final = '';
                    final = prec ? Num.to_fixed_fix(o_num, prec) : '' + Math.round(o_num);
                    final_seg = final.split('.');
                    if (final_seg[0].length > 3) {
                        final_seg[0] = final_seg[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, thousands_sep);
                    }
                    if ((final_seg[1] || '').length < prec) {
                        final_seg[1] = final_seg[1] || '';
                        final_seg[1] += new Array(prec - final_seg[1].length + 1).join('0');
                    }
                    return final_seg.join(dec_point);
                };
                /**
                 * Get X% by Y of Z.
                 * @param  {number} amount
                 * @param  {number} total
                 * @param  {number} percision
                 * @return {number}
                 */
                Num.get_percent = function (amount, total, percision) {
                    if (percision === void 0) { percision = 2; }
                    var count;
                    if (!amount || !total) {
                        return amount;
                    }
                    count = amount / total;
                    count = count * 100;
                    count = parseFloat(Num.format(count, percision));
                    return count;
                };
                /**
                 * Get X by Y% of Z
                 * @param {number} percent
                 * @param {number} total
                 * @param {number} percision
                 */
                Num.set_percent = function (percent, total, percision) {
                    if (percision === void 0) { percision = 2; }
                    var result;
                    if (!percent || !total) {
                        return 0;
                    }
                    result = parseFloat(Num.format((total / 100) * percent, percision));
                    return result;
                };
                return Num;
            })();
            common.Num = Num;
        })(common = js.common || (js.common = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
