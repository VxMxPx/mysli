;(function ($, MU) {

    'use strict';

    var template = '<div class="side panel"><div class="inner"></div></div>';

    var PanelSide = function (options) {
        options = $.extend({}, {
            header  : true,
            style   : null,
            content : null,
            footer  : false
        }, options);

        this.element = $('<div class="side panel" />');
        this._s = {};

        // header icons count
        this._s.headerItems = {left: 0, right: 0};

        if (options.header) {
            this.header(true);
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

        // add header's item
        // id      : string  unique item's id
        // options : object  item's properties:
        //      type           : string   link|title
        //      action         : string   action to be triggered on click
        //      preventDefault : boolean  weather to prevent default action
        //      icon           : string   icon name
        header_add : function (id, options) {
            var element,
                pos = 0,
                _this = this,
                action = options.action,
                preventDefault = options.preventDefault;

            switch (options.type) {
                case 'link':
                    element = $('<a href="#" />');
                    break;
                case 'title':
                    element = $('<h2/>');
                    break;
                default:
                    throw new Error('Invalid type: ' + options.type);
            }

            if (action) {
                element.on('click', function (e) {
                    if (preventDefault) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                    _this.element.trigger(action);
                });
            }
            if (options.icon) {
                element.append('<i class="fa fa-' + options.icon + '" />');
            }
            if (options.label) {
                element.append('<span>' + options.label + '</span>');
            }
            if (options.type !== 'title') {
                // position could be undefined, in that case, default to left
                options.position = options.position === 'right' ? 'right' : 'left';
                pos = ++this._s.headerItems[options.position];
                pos = (pos === 1 ? pos * 20 : pos * 25);
                element.css(options.position, pos + 'px');
            }
            element.appendTo(this.header());
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
        appendTo : function (parent) {
            this.element.appendTo(parent);
        }

    };

    MU.PanelSide = PanelSide;

}(Zepto, MU));
