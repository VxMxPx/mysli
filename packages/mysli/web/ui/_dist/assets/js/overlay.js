mysli.web.ui.overlay = (function () {

    'use strict';

    var ui = mysli.web.ui,
        template = '<div class="ui-overlay ui-widget"><div class="ui-overlay-busy"><i class="fa fa-cog fa-spin"></i></div></div>';

    var overlay = function () {
        this.$element = $(template);
        this.uid = ui._.uid(this.$element);
    };

    overlay.prototype = {
        constructor: overlay,

        event: ui._.extend.event([
            '<native>'
        ]),

        /**
         * Get/Set busy state.
         * @param  {Boolean} state
         * @return {Boolean}
         */
        busy: function(state) {
            if (typeof state !== 'undefined') {
                if (state) {
                    this.$element.addClass('state-busy');
                } else {
                    this.$element.removeClass('state-busy');
                }
            } else {
                this.$element.hasClass('state-busy');
            }
        },

        /**
         * Get/Set visibility state.
         * @param  {Boolean} status
         * @return {Boolean}
         */
        visible: function(status) {
            if (typeof status !== 'undefined') {
                if (status) {
                    this.$element.fadeIn();
                } else {
                    this.$element.fadeOut(400);
                }
            } else {
                return this.$element.is(':visible');
            }
        }
    };

    return overlay;

}());
