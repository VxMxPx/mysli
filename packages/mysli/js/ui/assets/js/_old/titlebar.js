mysli.js.ui.titlebar = (function () {

    'use strict';

    var ui = mysli.js.ui,
        common = mysli.js.common,
        template = '<div class="ui-widget ui-titlebar" />';

    var titlebar = function (options) {

        // Define element and uid
        this.$element = $(template);
        this.uid = ui.util.uid(this.$element);

        // Collection
        this.collection = new common.arr();

        // For box's methods
        this.$target = this.$element;

        // Properties
        this._ = common.merge({
            orientation: 'horizonatal',
            style: 'default',
            flat: false
        }, options);

        // Apply default options
        ui.util.use(this._, this, {
            style: 'style',
            flat: 'flat'
        });
    };

    titlebar.prototype = {

        constructor: titlebar,

        /**
         * Std. events
         */
        event: ui.extend.event(['<native>']),

        /**
         * Get/set flat state
         * @param  {Boolean} value
         * @return {Boolean}
         */
        flat: ui.extend.flat,

        /**
         * Get/Set style
         * @param {String} style
         * @type  {String}
         */
        style: ui.extend.style(['default', 'alt']),
    };

    ui.extend.collection(titlebar);

    return titlebar;

}());
