mysli.web.ui.button = (function () {

    'use strict';

    var ui = mysli.web.ui,
        template = '<button class="ui-button ui-widget" />';

    var button = function (label, options) {

        if (typeof label === 'string') {
            options.label = label;
        } else {
            options = label;
        }

        this._ = ui._.merge({
            label: null,
            icon: {
                name: null,
                position: 'left',
                spin: false
            },
            style: 'default',
            flat: false
        }, options);

        this.$element = $(template);

        ui.use(this.options, this, {
            label: 'label',
            icon: 'icon',
            style: 'style',
            flat: 'flat',
            disabled: 'disabled'
        });

        this.uid = us._.uid(this.$element);
    };

    button.prototype = {
        constructor: button,

        /**
         * Events
         */
        event: ui._.extend.event(['<native>']),

        /**
         * Get/Set button's style
         * @param {String} style
         * @type  {String}
         */
        style: ui._.extend.style(['default', 'alt', 'primary', 'confirm', 'attention']),

        /**
         * Get/Set disabled state.
         * @param {Boolean} state
         * @type  {Boolean}
         */
        disabled: ui._.extend.disabled,

        /**
         * Get/set button to be flat
         * @param  {Boolean} value
         * @return {Boolean}
         */
        flat: ui._.extend.flat,

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
        icon : function (icon) {
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
                    this._.icon = ui._.merge(this._.icon, icon);
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
