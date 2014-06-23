;(function ($, MU) {

    'use strict';

    // static private properties
    var count = 1,
        dimensions = {
            tiny  : 160,
            small : 260,
            medium: 500,
            big   : 800
        };

    // container : object  parent container
    // options   : object
    //      size      : string  tiny|small|medium|big
    //      front     : object  settings passed to the front side of panel
    //      back      : object  settings passes to the back side of panel
    //      expand    : boolean
    //      flippable : boolean
    //      shrink    : mixed   false|tiny|small|medium
    //      id        : mixed   string|false
    var Panel = function (container, options) {

        options = $.extend({}, {
            size      : 'small',
            front     : {},
            back      : {},
            expand    : false,
            flippable : false,
            closable  : true,
            shrink    : false,
            id        : false,
            position  : 0
        }, options);

        // panel's properties
        this.properties = {
            // position in px from left
            position : options.position,
            // when there's a lot of panels,
            // they start being pushed aside and partly hidden
            offset : 0,
            // weather panel is locked
            locked : false,
            // weather panel can be expanded to fill the available space
            expand : options.expand,
            // how much panel's width was increased (only if expand is true)
            expandFor : 0,
            // for how much weather panel can shrink (0 if can't shrink)
            shrink : 0,
            // panel size by word
            size : typeof dimensions[options.size] === 'undefined' ? 'small' : options.size,
            // panel's size by px
            width : 0,
            // list of child panels
            children : [],
            // is panel in away mode
            away : false,
            // if away on blur, then panel will go away when lose focus
            awayOnBlur : false,
            // the width (px) of panel when away
            awayWidth : 10,
            // if insensitive, then panel cannot be focused
            insensitive : false,
            // weather panel is busy (display loading indicator)
            busy : false,
            // if panel is full screen
            full : false,
            // when panel goes to full screen highest zIndex is set, this is the
            // original zIndex, to be restored, when full screen is turned off
            oldZIndex : 0,
            // panel's unique id
            id : (options.id ? options.id : 'mu-panel-' + count),
            // when true, some events will be prevented on the panel,
            // like further animations
            closing : false
        };

        // parent - container
        this.container = container;

        // front + back side
        this.front = false;
        this.back = false;

        // apply shrink
        this.shrink(options.shrink ? options.shrink : false);
        // apply width
        this.properties.width = dimensions[this.properties.size];

        // panel's dom element
        this.element = $('<div class="panel multi" id="' + this.properties.id + '" />');
        this.element.width(this.properties.width + 'px');
        // panel's sides
        this.sides = $('<div class="sides" />').appendTo(this.element);

        // add front side
        options.front.id = this.properties.id + '-side-front';
        this.front = new MU.PanelSide(this, options.front);
        this.front.style('front' + (options.front.style ? ' ' + options.front.style : ''));
        if (options.closable) {
            this.front.headerPrepend({
                icon           : 'times',
                type           : 'link',
                action         : 'self/close',
                preventDefault : true
            });
        }
        this.sides.append(this.front.element);

        // add back side
        if (options.flippable) {
            options.back.id = this.properties.id + '-side-back';
            this.back = new MU.PanelSide(this, options.back);
            this.back.style('back' + (options.back.style ? ' ' + options.back.style : ' alt'));
            this.back.headerPrepend({
                icon   : 'arrow-left',
                type   : 'link',
                action : 'self/flip'
            });
            this.sides.append(this.back.element);
        } else {
            this.sides.append('<div class="dummy"/>');
        }

        // increase panels count
        count++;
    };

    Panel.prototype = {

        constructor : Panel,

        // toggle panel flip (must be flippable)
        flip : function () {
            if (this.back) {
                this.element.toggleClass('flipped');
            }
        },

        // set away status
        // value   : boolean
        away : function (value) {
            var width;

            if (value) {
                if (this.hasFocus() || this.properties.away) {
                    this.properties.awayOnBlur = true;
                    return;
                }
                this.properties.away = true;
                width = -(this.properties.width - this.properties.awayWidth);
            } else {
                if (!this.properties.away) {
                    this.properties.awayOnBlur = false;
                    return;
                }
                this.properties.away = false;
                this.properties.awayOnBlur = false;
                width = this.properties.width - this.properties.awayWidth;
            }

            this.container.updateSum(width);
            this.container.refresh();
        },

        // set panel to be full screen
        // value : boolean
        full : function (value) {
            // calling it twice with the same value will mess things
            if (value === this.properties.full) { return; }
            if (value) {
                this.properties.full = true;
                this.container.focus(this.properties.id);
                this.properties.oldZIndex = this.element.css('z-index');
                this.element.css('z-index', 10000);
                this.element.animate({
                    left : 0,
                    width : '100%'
                });
            } else {
                this.properties.full = false;
                this.animate(function () {
                    this.element.css('z-index', this.properties.oldZIndex);
                });
            }
        },

        // if insensitive, then panel cannot be focused
        // value : boolean
        insensitive : function (value) {
            if (value) {
               if (this.hasFocus()) {
                    this.setFocus(false);
                    this.properties.insensitive = true;
                    this.container.focusNext(this.properties.id);
                } else {
                    this.properties.insensitive = true;
                }
            } else {
                this.properties.insensitive = false;
            }
        },

        // weather panel is busy
        // value : boolean
        busy : function (value) {
            if (this.properties.busy === value) { return; }
            if (value) {
                var busy = $('<div class="loading panel-busy" style="opacity:0;" />').prependTo(this.element);
                busy.animate({'opacity': 0.75});
                this.properties.busy = true;
            } else {
                this.element.find('div.panel-busy').fadeOut(400, function () {
                    this.remove();
                });
                this.properties.busy = false;
            }
        },

        // animate all the changes made to the element.
        animate : function (callback) {
            if (this.properties.closing) { return; }
            var _this = this;
            this.element.animate({
                left    : this.properties.position + this.properties.offset,
                width   : this.properties.width + this.properties.expandFor,
                opacity : 1
            }, 400, 'ease', function () {
                if (typeof callback === 'function') {
                    callback.call(_this);
                }
            });
        },

        // weather panel will be expanded to full the available space.
        // value : boolean
        expand : function (value) {
            this.properties.expand = !!value;
            this.container.refresh();
        },

        // when there's no more space, can panel shrink (instead of being offset)
        // value : string  false|tiny|small|medium
        shrink : function (value) {
            if (value) {
                this.properties.shrink = dimensions[value] ? dimensions[value] : dimensions.tiny;
            } else {
                this.properties.shrink = false;
            }
        },

        // set panel's size by word
        // value  : string   tiny|small|medium|big
        size : function (value) {
            var sizeDiff = 0;
            // set new size by word
            this.properties.size = typeof dimensions[value] === 'undefined' ? 'small' : value;
            sizeDiff = -(this.properties.width - dimensions[this.properties.size]);
            this.properties.width = dimensions[this.properties.size];
            this.animate();
            this.container.updateSum(sizeDiff, this.properties.id);
            this.container.refresh();
        },

        // add another panel which will depend on this one
        // it means, when this panel will be closed, child will be closed also
        // child : object  MU.Panel
        addChild : function (child) {
            this.properties.children.push(child);
        },

        // return : array
        getChildren : function () {
            return this.properties.children;
        },

        // set/get panel's zIndex.
        // value : integer
        zIndex : function (value) {
            if (typeof value === 'undefined') { return this.element.css('z-index'); }
            this.element.css({'z-index' : value});
        },

        // get focus state
        // return : boolean
        hasFocus : function () {
            return this.element.hasClass('selected');
        },

        // set panel's focus
        // value : boolean
        setFocus : function (value) {
            if (value) {
                this.element.addClass('selected');
                if (this.properties.away) {
                    this.away(false);
                    this.properties.awayOnBlur = true;
                }
            } else {
                this.element.removeClass('selected');
                if (this.properties.awayOnBlur) {
                    this.away(true);
                }
            }
        }
    };

    MU.Panel = Panel;

}(Zepto, Mysli.UI));
