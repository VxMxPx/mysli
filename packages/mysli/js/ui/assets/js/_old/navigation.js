mysli.js.ui.navigation = (function () {

    'use strict';

    var ui = mysli.js.ui,
        common = mysli.js.common,
        template = '<div class="ui-widget ui-navigation" />';

    var navigation = function () {

        // Define element and uid
        this.$element = $(template);
        this.uid = ui.util.uid(this.$element);

        // Collection
        this.collection = new common.arr();

        // Click action to ui event
        this.$element.on('click', '.ui-navigation-item', function (e) {
            var id;
            e.stopPropagation();
            id = e.currentTarget.id.substr(6);
            this.event.trigger('action', [id]);
        }.bind(this));
    };

    navigation.prototype = {

        constructor: navigation,

        event: ui.extend.event([
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
        style: ui.extend.style(['default', 'alt']),

        /**
         * Add new navigation item.
         * @param {mixed}  options string: label | object: addition options
         * @param {string} id
         */
        push: function(options, id) {
            var button;

            if (typeof options === 'string') {
                options = {label: options};
            }

            // $item = $(template_item);
            // $item.attr('id', 'ui-nav-item-id-'+id);

            button = new ui.button(options);
            // $item.append(button.$element);

            // this.items[id] = button;
            // this.$element.append($item);

            return this.collection_push(button, id);
        }
    };

    ui.extend.collection(navigation, {
        push: function (_, __, uid) {
            return '<div class="ui-navigation-item collection-target" id="ui-nav-item-id-'+uid+'" />';
        }
    });

    return navigation;

}());
