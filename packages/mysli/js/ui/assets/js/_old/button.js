mysli.js.ui.button = (function () {

    'use strict';

    var ui = mysli.js.ui,
        common = mysli.js.common,
        template = '<button class="ui-button ui-widget" />';

    var button = function (label, options) {

        // Define element and uid
        this.$element = $(template);
        this.uid = ui.util.uid(this.$element);

        // Push label to options
        if (typeof label === 'string') {
            options.label = label;
        } else {
            options = label;
        }

        // Properties
        this._ = common.merge({
            label: null,
            icon: {
                name: null,
                position: 'left',
                spin: false
            },
            style: 'default',
            flat: false
        }, options);

        // Apply defaults
        ui.util.use(this.options, this, {
            label: 'label',
            icon: 'icon',
            style: 'style',
            flat: 'flat',
            disabled: 'disabled'
        });
    };

    button.prototype = {

        constructor: button,

        /**
         * Events
         */
        event: ui.extend.event(['<native>']),

        /**
         * Get/Set button's style
         * @param {String} style
         * @type  {String}
         */
        style: ui.extend.style(['default', 'alt', 'primary', 'confirm', 'attention']),

        /**
         * Get/Set disabled state.
         * @param {Boolean} state
         * @type  {Boolean}
         */
        disabled: ui.extend.disabled,

        /**
         * Get/set button to be flat
         * @param  {Boolean} value
         * @return {Boolean}
         */
        flat: ui.extend.flat,

        /**
         * Get/Set button's label.
         * @param  {String} value
         * @return {String}
         */
        label: function(value) {
            var $label = this.$element.find('span.label'),
                method;

            if (typeof value !== 'undefined') {
                if (!value) {
                    $label.remove();
                }

                if (!$label.length) {
                    $label = $('<span class="label" />');
                    method = this._.icon.position === 'right' ? 'prepend' : 'append';
                    this.$element[method]($label);
                }

                $label.text(value);
            } else {
                return $label.text();
            }
        },

        /**
         * Get/Set Button's Icon
         * @param  {Mixed}  String: icon || Object: {icon: 0, position: 0, spin: 0} || false: remove icon
         * @return {Object} {icon: 0, position: 0, spin: 0}
         */
        icon: function (icon) {
            var $icon, method, spin;

            if (typeof icon !== 'undefined') {

                $icon = this.$element.find('i.fa');
                $icon.remove();

                if (icon === false) {
                    return;
                }

                if (typeof icon === 'string') {
                    this._.icon.name = icon;
                } else {
                    this._.icon = common.merge(this._.icon, icon);
                }

                method = this._.icon.position === 'right' ? 'append' : 'prepend';
                spin = this._.icon.spin ? ' fa-spin' : '';

                this.$element[method]($('<i class="fa fa-'+this._.icon.name+spin+'" />'));
            } else {
                return this._.icon;
            }
        }
    };

    return button;
}());
