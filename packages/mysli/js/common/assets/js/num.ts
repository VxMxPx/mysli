module mysli.js.common {
    export class Num {

        static to_fixed_fix(n:number, prec:number):string {
            var k:number;
            k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        }

        /**
         * Format number
         * @param  {number} number
         * @param  {number} decimals
         * @param  {string} dec_point
         * @param  {string} thousands_sep
         * @return {string}
         */
        static format(number:number, decimals:number, dec_point:string='.', thousands_sep:string=','):string {
            var s_num: string;
            var o_num: number;
            var prec: number;
            var final: string;
            var final_seg: string[];

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
        }

        /**
         * Get X% by Y of Z.
         * @param  {number} amount
         * @param  {number} total
         * @param  {number} percision
         * @return {number}
         */
        static get_percent(amount:number, total:number, percision:number=2):number {
            var count: number;

            if (!amount || !total) {
                return amount;
            }

            count = amount / total;
            count = count * 100;
            count = parseFloat(Num.format(count, percision));

            return count;
        }

        /**
         * Get X by Y% of Z
         * @param {number} percent
         * @param {number} total
         * @param {number} percision
         */
        static set_percent(percent:number, total:number, percision:number=2):number {
            var result: number;

            if (!percent || !total) {
                return 0;
            }

            result = parseFloat(Num.format((total / 100) * percent, percision));

            return result;
        }
    }
}
