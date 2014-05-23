;(function ($, MU) {

    'use strict';

    var Button = function (element, options) {
        this.element = $(element);

        this.label = this.element.find('span');
        if (!this.label.length) {
            this.element.text('<span>' + this.element.text() + '</span>');
            this.label = this.element.find('span');
        }

        // Those are original values of element, before busy state is set...
        this.originalContent = {
            'content' : '',
            'disabled': null
        };
    };

    Button.prototype = {

        constructor : Button,

        // get button element
        // return : object
        element : function () {
            return this.element;
        },

        // add event handlers to the element
        on : function (evnt, call) {
            return this.element.on(evnt, call);
        },

        // detach event handlers added with on
        off : function (evnt) {
            return this.element.off(evnt);
        },

        // set /get disabled state
        // state  : boolean
        // return : boolean
        disabled : function (state) {
            if (typeof state === 'undefined') {
                state = this.element.attr('disabled');
                return (state === 'true' || state === 'disabled');
            }

            if (state) {
                this.element.attr('disabled', true);
            } else {
                this.element.removeAttr('disabled');
            }
        },

        // set / get pressed state
        // state  : boolean
        // return : boolean
        pressed : function (state) {
            if (typeof state === 'undefined') {
                return this.element.hasClass('pressed');
            }

            if (state) {
                this.element.addClass('pressed');
            } else {
                this.element.removeClass('pressed');
            }
        },

        // set / get button's style
        // variant: string (alt, primary, attention, default)
        // return : string
        style : function (variant) {
            var classes = 'alt primary attention';

            // Get style
            if (typeof variant === 'undefined') {
                for (var i = classes.split(' ').length - 1; i >= 0; i--) {
                    if (this.element.hasClass(classes[i])) return classes[i];
                }
                return 'default';
            }

            // Set style
            this.element.removeClass(classes);

            if (variant !== 'default') {
                this.element.addClass(variant);
            }
        },

        // set /get label
        // text   : string
        // return : string
        label : function (text) {
            if (typeof text === 'undefined') {
                return this.label.text();
            }
            this.label.text(text);
        },

        // set / get button busy state
        // state : boolean
        // return: boolean
        busy : function (state, label) {
            if (typeof state === 'undefined') {
                return this.element.hasClass('busy');
            }

            if (state) {
                if (this.busy()) { return; }
                this.element.addClass('busy');
                this.originalContent.content = this.element.html();
                this.originalContent.disabled = this.disabled();
                this.element.html(' ' + (label ? label : this.label()));
                this.icon('spinner', 'left', true);
                this.disabled(true);
            } else {
                if ( ! this.busy()) { return; }
                this.element.removeClass('busy');
                this.element.html(this.originalContent.content);
                this.disabled(this.originalContent.disabled);
            }
        },

        // set /get button's icon
        // name     : string
        // position : string (left, right)
        // return   : string
        icon : function (name, position, spin) {
            var icon = this.element.find('i');

            if (typeof name === 'undefined') {
                if (!icon.length) return false;
                return icon.attr('class').match(/fa-([a-z\-]+)/)[1];
            }

            icon.remove(); // Remove icons in any case...
            if (!name) return; // icons were removed, and replacement not needed.
            this
                .element[(position === 'right' ? 'append' : 'prepend')]('<i></i>')
                .find('i')
                .removeClass()
                .addClass('fa fa-' + name + (spin ? ' fa-spin' : ''));
        }
    };

    MU.Button = Button;

}(Zepto, MU));
