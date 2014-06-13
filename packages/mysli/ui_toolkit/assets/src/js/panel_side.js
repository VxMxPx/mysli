;(function ($, MU) {

    'use strict';

    var count = 1;

    // create generic header element
    // parent   : object
    // options  : object
    function createHeaderElement(parent, options) {
        var element,
            action = options.action,
            preventDefault = options.preventDefault;

        // more types will be added
        switch (options.type) {
            case 'costume':
                element = $(options.element);
                break;
            default:
                element = $('<a href="#" />');
                // throw new Error('Invalid type: ' + options.type);
        }

        // id
        if (options.id) {
            element.prop('id', id);
        }

        // click
        if (action) {
            element.on('click', function (e) {
                if (preventDefault) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                parent.element.trigger(action);
            });
        }

        // icon?
        if (options.icon) {
            element.append('<i class="fa fa-' + options.icon + '" />');
        }

        // label
        if (options.label) {
            element.append('<span>' + options.label + '</span>');
        }

        return element;
    }

    // container : object  parent
    // options   : object
    //      id      : strign   this side's unique id
    //      header  : boolean  weather header should be added
    //      title   : string   panel title
    //      style   : string   alt|default
    //      content : string   html
    //      footer  : boolean  weather footer should be added
    var PanelSide = function (container, options) {

        options = $.extend({}, {
            id      : null,
            header  : true,
            title   : false,
            style   : null,
            content : null,
            footer  : false
        }, options);

        // side's parent
        this.container = container;

        this.properties = {
            id : (options.id ? options.id : 'panel-side' + count),
            headerItems : {left: 0, right: 0}
        };

        // collection of header elements
        this.headerElements = [];

        this.element = $('<div class="side panel" id="' + this.properties.id + '" />');
        this.contentContainer = $('<div class="body" />').appendTo(this.element);

        if (options.header) {
            this.header(true);
        }

        if (options.title) {
            this.title(options.title);
        }

        if (options.style) {
            this.style(options.style);
        }

        if (options.content) {
            this.content(options.content);
        }

        count++;
    };

    PanelSide.prototype = {

        constructor : PanelSide,

        // add/remove/get header
        // value  : boolean  true - append header, false - remove header
        // return : object   $('header')
        header : function (value) {
            if (typeof value === 'undefined') { return this.element.find('header.main'); }
            if (value) {
                if (!this.header().length) {
                    this.element.prepend('<header class="main"><h2></h2></header>');
                }
            } else {
                this.element.remove('header.main');
            }
        },

        // set/get title
        // value  : string  if undefined title value will be returned.
        // return : string
        title : function (value) {
            if (typeof value === 'undefined') { return this.header().find('h2').text(); }
            this.header().find('h2').text(value);
        },

        // append/prepend header element
        // options : object  item's properties:
        //      id             : string   unique item's id
        //      type           : string   link
        //      action         : string   action to be triggered on click
        //      preventDefault : boolean  weather to prevent default action
        //      icon           : string   icon name
        headerAppend : function (options) {
            var pos = null,
                position = null,
                element = createHeaderElement(this, options);

            // if type is costume, no positioning will be done
            if (options.type !== 'costume') {
                position = 'right';
                pos = ++this.properties.headerItems[position];
                pos = (pos === 1 ? pos * 20 : pos * 25);
                element.css(position, pos + 'px');
            }

            element.appendTo(this.header());
            this.headerElements.push(element);
        },
        headerPrepend : function (options) {
            var pos = null,
                position = null,
                element = createHeaderElement(this, options);

            // if type is costume, no positioning will be done
            if (options.type !== 'costume') {
                position = 'left';
                pos = ++this.properties.headerItems[position];
                pos = (pos === 1 ? pos * 20 : pos * 25);
                element.css(position, pos + 'px');
            }

            element.prependTo(this.header());
            this.headerElements.unshift(element);
        },

        // get/set content
        // content : string  html
        content : function (content) {
            if (typeof content === 'undefined') { return this.contentContainer.html(); }
            this.contentContainer.html(content);
        },

        // add/remove/get footer
        // value  : boolean  true - append footer, false - remove footer
        // return : object   $('footer')
        footer : function (value) {
            if (typeof value === 'undefined') { return this.element.find('footer.main'); }
            if (value) {
                if (!this.footer().length) {
                    this.element.append('<footer class="main"><h2></h2></footer>');
                }
            } else {
                this.element.remove('footer.main');
            }
        },

        // Get / set style
        // variant: string (alt, default, ...)
        // return : string
        style : function (style) {
            if (typeof style === 'undefined') { return this.element.prop('class'); }
            this.element.addClass(style);
        }
    };

    MU.PanelSide = PanelSide;

}(Zepto, MU));
