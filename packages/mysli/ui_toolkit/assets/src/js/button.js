;(function ($, MU) {

    var Button = function (element, options) {
        this.$element = $(element);

        this.$label = this.$element.find('span');
        if (!this.$label.length) {
            this.$element.text('<span>' + this.$element.text() + '</span>');
            this.$label = this.$element.find('span');
        }
    };

    Button.prototype = {

        constructor : Button,

        // return: boolean
        is_disabled : function () {
            var state = this.$element.attr('disabled');
            return (state === 'true' || state === 'disabled');
        },

        // set button to be disabled
        // state: boolean
        disabled : function (state) {
            if (state)
                this.$element.attr('disabled', true);
            else
                this.$element.removeAttr('disabled');
        },

        // return: boolean
        is_pressed : function () {
            return this.$element.hasClass('pressed');
        },

        // set button pressed on/off
        // state: boolean
        pressed : function (state) {
            if (state)
                this.$element.addClass('pressed');
            else
                this.$element.removeClass('pressed');
        },

        // get current button's style:
        // return: string (alt, primary, attention, default)
        get_style : function () {
            if      (this.$element.hasClass('alt'))       return 'alt';
            else if (this.$element.hasClass('primary'))   return 'primary';
            else if (this.$element.hasClass('attention')) return 'attention';
            else                                          return 'default';
        },

        // set button's style
        // variant: string (alt, primary, attention, default)
        style : function (variant) {
            var classes = 'alt primary attention';
            this.$element.removeClass(classes);
            if (variant !== 'default')
                this.$element.addClass(variant);
        },

        // get button's label
        // return: string
        get_lablel : function () {
            return this.$label.text();
        },

        // set label
        // text: string
        label : function (text) {
            this.$label.text(text);
        },

        // is button busy
        // return: boolean
        is_busy : function () {
            return this.$element.hasClass('busy');
        },

        // set button busy
        // state: boolean
        busy : function (state) {
            if (state)
                this.$element.addClass('busy');
            else
                this.$element.removeClass('busy');
        },

        // get current icon
        // return: string | false (if no icon)
        get_icon : function () {
            var icon = this.$element.find('i');
            if (!icon.length) return false;
            return icon.attr('class').match(/fa-([a-z\-]+)/)[1];
        },

        // set button's icon
        // name     : string
        // position : string (left, right)
        icon : function (name, position) {
            this.$element.find('i').remove(); // Remove icons in any case...
            if (!name) return; // icons were removed, and replacement not needed.
            this
                .$element[(position === 'left' ? 'prepend' : 'append')]('<i></i>')
                .find('i')
                .removeClass()
                .addClass('fa fa-' + name);
        }
    };

    MU.Button = Button;
}(Zepto, MU));
