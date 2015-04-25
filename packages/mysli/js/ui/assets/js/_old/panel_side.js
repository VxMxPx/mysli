mysli.js.ui.panel_side = (function () {

    'use strict';

    var ui = mysli.js.ui,
        common = mysli.js.common,
        template = '<div class="ui-widget ui-panel-container ui-panel-side" />';

    var panel_side = function (options) {

        // Define element and uid
        this.$element = $(template);
        this.uid = ui.util.uid(this.$element);

        // Collections
        this.collection = new common.arr();

        // Apply default options
        ui.util.use(options, this, {
            style: 'style'
        });
    };

    panel_side.prototype = {

        constructor: panel_side,

        /**
         * Std. event
         */
        event: ui.extend.event(['<native>']),

        /**
         * Set panel's side style
         */
        style: ui.extend.style(['default', 'alt']),
    };

    ui.extend.collection(panel_side);

    return panel_side;

}());
