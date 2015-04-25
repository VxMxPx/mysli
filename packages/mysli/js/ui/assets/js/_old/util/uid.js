mysli.js.ui.util.uid = (function () {

    'use strict';

    var uidc = 0;

    /**
     * Get new unique ID for an object.
     * @param  {Object} element
     * @return {string}
     */
    return function (element) {
        var uuid = "muid-"+(++uidc);
        element.addClass(uuid);
        return uuid;
    };

}());
