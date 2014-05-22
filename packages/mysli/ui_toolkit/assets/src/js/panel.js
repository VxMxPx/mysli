;(function ($, MU) {

    'use strict';

    var count = 0,
        dimensions = {
            tiny  : 160,
            small : 260,
            medium: 500,
            big   : 800,
            full  : 0
        };

    // options : object
    //      size      : string  tiny|small|medium|big|full
    //      expand    : boolean
    //      flippable : boolean
    //      shrink    : mixed   false|tiny|small|medium
    //      id        : mixed   string|false
    var Panel = function (options) {

        var _this = this;

        options = $.extend({}, {
            size      : 'small',
            expand    : false,
            flippable : false,
            shrink    : false,
            id        : false,
            position  : 0
        }, options);

        // Current panel's state
        this._s = {};

        // Position in px from left
        this._s.position = options.position;

        // When there's a lot of panels,
        // they start beeing pushed aside and partly hidden.
        this._s.offset = 0;

        // Panel lock state
        this._s.locked = false;

        // Exapnd state
        this._s.expand = options.expand;

        // Weather panel can shrink
        this.shrink(options.shrink ? options.shrink : false);

        // If expand is allowed, then here's how much panel's width was increased!
        this._s.expandedFor = 0;

        // Set panel's size
        this._s.size = 0;
        this.size(options.size);

        // List of childs panels which will be closed together with this one!
        this._s.children = [];

        // DOM elements
        this.element = (function () {
            var wrapper = $('<div class="panel multi" />'),
                sides = $('<div class="sides" />').appendTo(wrapper);

            wrapper.width((this._s.width ? this._s.width + 'px' : '100%'));

            // Append front side
            // var sides = wrapper.find('sides');

            this.front = new MU.PanelSide(options.front);
            this.front.style('front');
            this.front.append(sides);

            // Append back side
            if (options.flippable) {
                this.back = new MU.PanelSide(options.back);
                this.back.style('back alt');
                this.back.append(sides);
            } else {
                this.back = false;
                sides.append('<div class="dummy"/>');
            }

            return wrapper;
        }).call(this);

        // Assign ID
        this.id((options.id ? options.id : 'mu-panel-' + count));

        // When this is true, some events will be prevented on the panel
        // Like further animations
        this.closing = false;

        // Increase number of panels!
        count++;
    };

    Panel.prototype = {

        constructor : Panel,

        // Animate all the changes made to the element.
        animate : function () {
            if (this.closing) { return; }
            this.element.animate({
                left    : this._s.position + this._s.offset,
                width   : this.width + this._s.expandedFor,
                opacity : 1
            }, 400, 'ease');
        },

        // Set locked state. If no value is provided, locked state will be returned.
        // value : boolean
        locked : function (value) {
            if (value === undefined) { return this._s.locked; }
            this._s.locked = !!value;
        },

        // Take the amount in px, (how far the panel should be pushed).
        // If no value is provided, return current offset.
        // offset : integer  amount of px to be pushed to the left.
        offset : function (offset) {
            if (offset === undefined) { return this._s.offset; }
            this._s.offset = offset;
        },

        // Set panel's position (left). If not position if provided,
        // return current position.
        // position : integer
        position : function (position) {
            if (position === undefined) { return this._s.position; }
            this._s.position = position;
        },

        // Weather panel will be expanded to full the available space.
        // If no value provided, current state is returned.
        // value : boolean
        expand : function (value) {
            if (value === undefined) { return this._s.expand; }
            this._s.expand = !!value;
        },

        // For how much panel should be epanded.
        // width : integer
        expandFor : function (width) {
            if (width === undefined) { return this._s.expandedFor; }
            this._s.expandedFor = width;
        },

        // When there's no more space, can panel shrink (instead of being offseted).
        // If no value provided, current state is returned.
        // value : string  false|tiny|small|medium
        shrink : function (value) {
            if (value === undefined) { return this._s.shrink; }
            if (value === false) {
                this._s.shrink = false;
            } else {
                this._s.shrink = dimensions[value] ? dimensions[value] : dimensions.tiny;
            }
        },

        // Panel's size (by name).
        // If no value provided, current state is returned.
        // value : string  tiny|small|medium|big|full
        size : function (value) {
            if (value === undefined) { return this._s.width; }
            this._s.size = typeof dimensions[value] === 'undefined' ? 'small' : value;
            this._s.width = dimensions[this._s.size];
        },

        // Return width in px.
        // return : integer
        width : function () {
            return this._s.width;
        },

        // Set / get id.
        // id : string
        id : function (id) {
            if (id === undefined) { return this.element.prop('id'); }
            this.element.prop('id', id);
        },

        // Add another panel which will depend on this one. - It means, when this
        // panel will be closed, child will be closed also.
        // child : object  MU.Panel
        addChild : function (child) {
            this._s.children.push(child);
        },

        // return : array
        getChildren : function () {
            return this._s.children;
        },

        // Set / get panel's zIndex.
        // value : integer
        zIndex : function (value) {
            if (value === undefined) { return this.element.css('z-index'); }
            this.element.css({'z-index' : value});
        },

        // Turn this panel's focus on/off
        // state : boolean
        focus : function (state) {
            if (state === undefined) { return this.element.hasClass('selected'); }
            if (state) {
                this.element.addClass('selected');
            } else {
                this.element.removeClass('selected');
            }
        },

        // Remove this panel
        remove : function () {
            var that = this.element;

            if (this.locked()) {
                throw new Error('Panel is locked, cannot close it!');
            }

            this.closing = true;

            this.element.animate({
                left    : (this._s.position + this._s.offset) - (this.width + this._s.expandedFor) - 10,
                opacity : 0
            }, 'normal', function () {
                that.remove();
            });

            if (this._s.children.length) {
                for (var i = this._s.children.length - 1; i >= 0; i--) {
                    this._s.children[i].remove();
                }
            }
        },

        // Append this panel to the parent
        // parent : string
        append : function (parent) {
            this.element.appendTo(parent);
        }
    };

    MU.Panel = Panel;

}(Zepto, MU));
