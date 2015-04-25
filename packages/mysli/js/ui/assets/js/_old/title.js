mysli.js.ui.title = (function () {

    'use strict';

    var ui = mysli.js.ui,
        common = mysli.js.common,
        template = '<span class="ui-widget ui-title" />';

    var title = function (text, options) {

        // Define element and uid
        this.$element = $(template);
        this.uid = ui.util.uid(this.$element);

        // Push text to options
        if (typeof options !== 'object') {
            options = {};
        }
        if (typeof text === 'object') {
            options = text;
        } else {
            options.text = text;
        }

        // Properties
        this._ = common.merge({
            text: null,
            level: 1
        }, options);

        // Apply default options
        ui.util.use(this._, this, {
            level: 'level',
            text: 'text'
        });
    };

    title.prototype = {

        constructor: title,

        /**
         * Standard event
         */
        event: ui.extend.event(['<native>']),

        /**
         * Get/Set Title's Value.
         * @param  {Integer} value 1-6
         * @return {Integer}
         */
        level: function (value) {
            if (typeof value !== 'undefined') {
                if (value < 1 || value > 6) {
                    throw new Error("Level needs to be a numeric value between 1 and 6.");
                }
                this._.level = value;
                this.$element.html('<h'+value+' />');
                this.text(this._.text);
            } else {
                return this._.level;
            }
        },

        /**
         * Get/Set Text.
         * @param  {String} value
         * @return {String}
         */
        text: function (value) {
            if (typeof value !== 'undefined') {
                this._.text = value;
                this.$element.find(':first-child').text(value);
            } else {
                return this._.text;
            }
        }
    };

    return title;

}());
