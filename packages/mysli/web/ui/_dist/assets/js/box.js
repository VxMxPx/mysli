mysli.web.ui.box = (function () {

    'use strict';

    var ui = mysli.web.ui,
        template = '<div class="ui-box ui-widget" />';

    var box = function (options) {
        this.$element = $(template);
        this.uid = ui._.uid(this.$element);
        this._ = ui._.merge({
            orientation: 'horizontal'
        }, options);
        if (this.orientation === 'vertical') {
            var row = $('<div class="row" />');
            this.$element.append(row);
            this.$target = row;
        } else {
            this.$target = this.$element;
        }
    };

    box.prototype = {

        constructor: box,

        event: ui._.extend.event([
            // Support for native dom events.
            '<native>'
        ]),

        /**
         * Add new vidget to the box.
         * @param  {Object}  widget
         * @param  {Boolean} prepend weather to prepend widget rather than append
         * @param  {Boolean} expand  weather cell should expand to max width/height
         * @return {String}  uid
         */
        add: function (widget, prepend, expand) {
            var expanded = expand ? 'expanded' : 'collapsed',
                method = prepend ? 'prepend' : 'append',
                uid = 'mwub-'+widget.uid,
                wrapper = null;

            if (this.orientation === 'horizontal') {
                wrapper = $('<div class="row '+expanded+' '+uid+'" />');
                $('<div class="cell" />')
                    .append(widget.$element)
                    .appendTo(wrapper);
            } else {
                wrapper = $('<div class="cell '+expanded+' '+uid+'" />');
            }

            this.$target[method](wrapper);
            return uid;
        },

        /**
         * Remove element by:
         * @param {Mixed} selector widget | uid
         */
        remove: function (selector) {
            if (typeof selector === 'object') {
                selector = 'mwub-'+selector.uid;
            }
            this.$target.find(selector).remove();
        }
    };

    return box;

}());
