mysli.web.ui.panel_side = (function () {

    'use strict';

    var ui = mysli.web.ui,
        template = '<div class="ui-widget ui-panel-container ui-panel-side" />';

    var panel_side = function (options) {
        this.$element = $(template);
        this.uid = ui._.uid(this.$element);

        ui._.use(options, this, {
            style: 'style'
        });
    };

    panel_side.prototype = {
        constructor: panel_side,

        /**
         * Std. event
         */
        event: ui._.extend.event(['<native>']),

        /**
         * Set panel's side style
         */
        style: ui._.extend.style(['default', 'alt']),
    };

    return panel_side;

}());
