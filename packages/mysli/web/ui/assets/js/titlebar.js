mysli.web.ui.titlebar = (function () {

    'use strict';

    var ui = mysli.web.ui,
        template = '<div class="ui-widget ui-titlebar" />';

    var titlebar = function () {
        this.$element = $(template);
        this.uid = ui._.uid(this.$element);

        // For box's methods
        this.$target = this.$element;
        this._.orientation = 'horizonatal';
    };

    titlebar.prototype = {
        constructor: titlebar,

        event: ui._.extend.event(['<native>']),

        /**
         * Add new vidget to the titlebar.
         * @param  {Object}  widget
         * @param  {Boolean} prepend weather to prepend widget rather than append
         * @param  {Boolean} expand  weather cell should expand to max width/height
         * @return {String}  uid
         */
        add: ui.box.add,

        /**
         * Remove wiget by:
         * @param {Mixed} selector widget | uid
         */
        remove: ui.box.remove,

        /**
         * Get/set flat state
         * @param  {Boolean} value
         * @return {Boolean}
         */
        flat: ui._.extend.flat,
    };

    return titlebar;

}());
