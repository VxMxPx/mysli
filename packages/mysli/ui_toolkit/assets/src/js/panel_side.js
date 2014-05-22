;(function ($, MU) {

    'use strict';

    var template = '<div class="side panel"><div class="inner"></div></div>';

    var PanelSide = function (options) {
        options = $.extend({}, {
            header  : true,
            title   : null,
            style   : null,
            close   : true,
            flip    : false,
            content : null,
            menu    : null,
            footer  : false
        }, options);

        this.element = $('<div class="side panel" />');

        if (options.header) {
            this.header(true);
        }

        if (options.title) {
            this.title(options.title);
        }

        if (options.style) {
            this.style(options.style);
        }
    };

    PanelSide.prototype = {

        constructor : PanelSide,

        // add/remove/get header
        // value  : boolean  true - append header, false - remove header
        // return : object   $('header')
        header : function (value) {
            if (value === undefined) { return this.element.find('header.main'); }
            if (value) {
                if (!this.header().length) {
                    this.element.prepend('<header class="main"><h2></h2></header>');
                }
            } else {
                this.element.remove('header.main');
            }
        },

        // add/remove/get footer
        // value  : boolean  true - append footer, false - remove footer
        // return : object   $('footer')
        footer : function (value) {
            if (value === undefined) { return this.element.find('footer.main'); }
            if (value) {
                if (!this.footer().length) {
                    this.element.append('<footer class="main"><h2></h2></footer>');
                }
            } else {
                this.element.remove('footer.main');
            }
        },

        // Get / set title.
        // value : string
        title : function (value) {
            if (!this.header()) { return; }
            if (value === undefined) { return this.header().find('h2').text(); }
            this.header().find('h2').text(value);
        },

        // Get / set style
        // variant: string (alt, default)
        // return : string
        style : function (variant) {
            var classes = 'alt';

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

        // Append panel side to the parent
        // parent : object  MU.Panel
        append : function (parent) {
            this.element.appendTo(parent);
        }

    };

    MU.PanelSide = PanelSide;

}(Zepto, MU));
