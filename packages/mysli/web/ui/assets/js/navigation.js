mysli.web.ui.navigation = (function () {

    'use strict';

    var ui = mysli.web.ui,
        template = '<div class="ui-widget ui-navigation" />',
        template_item = '<div class="ui-navigation-item" />';

    var navigation = function () {
        this.$element = $(template);
        this.uid = ui._.uid(this.$element);

        // Collection of actual button objects
        this.items = {};

        this.$element.on('click', '.ui-navigation-item', function (e) {
            var id;
            e.stopPropagation();
            id = e.currentTarget.id.substr(6);
            this.event.trigger('action', [id]);
        }.bind(this));
    };

    navigation.prototype = {
        constructor: navigation,

        event: ui._.extend.event([
            '<native>',
            // # When any navigation item is cliked
            // # => ( string item_id, object this )
            'action'
        ]),

        /**
         * Get/Set navigation style.
         * @param {String} style
         * @type  {String}
         */
        style: ui._.extend.style(['default', 'alt']),

        /**
         * Add new navigation item.
         * @param {String} id
         * @param {Mixed}  options string: label | object: addition options
         */
        add: function(id, options) {
            var $item, button;

            if (typeof options === 'string') {
                options = {label: options};
            }

            $item = $(template_item);
            $item.attr('id', 'ui-nav-item-id-'+id);

            button = new ui.button(options);
            $item.append(button.$element);

            this.items[id] = button;
            this.$element.append($item);
        },

        /**
         * Get item (button object) by ID
         * @param  {String} id
         * @return {Object}
         */
        get: function(id) {
            return this.items[id];
        },

        /**
         * Remove navigation item.
         * @param  {String} id
         */
        remove: function(id) {
            delete this.items[id];
            this.$element.find('#ui-nav-item-id-'+id).remove();
        }
    };

    return navigation;

}());
