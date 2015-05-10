/// <reference path="_jquery.d.ts" />
module mysli.js.common {

    export function mix(defaults: any, options: any = {}): any {
        return $.extend({}, defaults, options);
    }
}
