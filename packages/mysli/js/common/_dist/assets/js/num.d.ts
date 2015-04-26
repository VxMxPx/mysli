declare module mysli.js.common {
    class Num {
        static to_fixed_fix(n: number, prec: number): string;
        /**
         * Format number
         * @param  {number} number
         * @param  {number} decimals
         * @param  {string} dec_point
         * @param  {string} thousands_sep
         * @return {string}
         */
        static format(number: number, decimals: number, dec_point?: string, thousands_sep?: string): string;
        /**
         * Get X% by Y of Z.
         * @param  {number} amount
         * @param  {number} total
         * @param  {number} percision
         * @return {number}
         */
        static get_percent(amount: number, total: number, percision?: number): number;
        /**
         * Get X by Y% of Z
         * @param {number} percent
         * @param {number} total
         * @param {number} percision
         */
        static set_percent(percent: number, total: number, percision?: number): number;
    }
}
