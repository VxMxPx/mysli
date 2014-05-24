;(function ($, MU) {

    'use strict';

    var count = 0,
        dimensions = {
            tiny  : 160,
            small : 260,
            medium: 500,
            big   : 800
        };

    // options : object
    //      size      : string  tiny|small|medium|big
    //      expand    : boolean
    //      flippable : boolean
    //      shrink    : mixed   false|tiny|small|medium
    //      id        : mixed   string|false
    var Panel = function (options) {

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
        this._s.expandFor = 0;

        // Set panel's size
        this._s.size = typeof dimensions[options.size] === 'undefined' ? 'small' : options.size;
        this._s.width = dimensions[this._s.size];

        // List of childs panels which will be closed together with this one!
        this._s.children = [];

        // Is panel in away mode
        // If away on blur, then panel will go away when lose focus!
        // Away width is the size of panel when away.
        this._s.away = false;
        this._s.awayOnBlur = false;
        this._s.awayWidth = 10;

        // If insensitive, then panel cannot be focused
        this._s.insensitive = false;

        // weather panel is busy
        this._s.busy = false;

        // If full screen
        this._s.full = false;
        this._s.oldZIndex = 0;

        // Id
        options.id = (options.id ? options.id : 'mu-panel-' + count);

        // DOM elements
        this.element = (function () {
            var wrapper = $('<div class="panel multi" id="' + options.id + '" />'),
                sides = $('<div class="sides" />').appendTo(wrapper);

            wrapper.width(this._s.width + 'px');

            options.front.id = options.id + '-side-front';
            this.front = new MU.PanelSide(options.front);
            this.front.style('front');
            if (options.closable) {
                this.front.header_add('close', {
                    icon           : 'times',
                    type           : 'link',
                    action         : 'self/close',
                    preventDefault : true
                });
            }
            if (options.front.title) {
                this.front.header_add('title', {
                    label : options.front.title,
                    type  : 'title'
                });
            }
            sides.append(this.front.element);


            // Append back side
            if (options.flippable) {
                options.back.id = options.id + '-side-back';
                this.back = new MU.PanelSide(options.back);
                this.back.style('back alt');
                this.back.header_add('close', {
                    icon   : 'arrow-left',
                    type   : 'link',
                    action : 'self/flip'
                });
                if (options.back.title) {
                    this.back.header_add('title', {
                        label : options.front.title,
                        type  : 'title'
                    });
                }
                sides.append(this.back.element);
            } else {
                this.back = false;
                sides.append('<div class="dummy"/>');
            }

            return wrapper;
        }).call(this);

        // When this is true, some events will be prevented on the panel
        // Like further animations
        this._s.closing = false;

        // Increase number of panels!
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

        // get/set away status
        // value   : boolean
        // refresh : boolean  weather refresh even should be tirggered
        // return  : boolean
        away : function (value, refresh) {
            var width;

            if (value === undefined) { return this._s.away; }

            if (value) {
                if (this.focus() || this._s.away) {
                    this._s.awayOnBlur = true;
                    return;
                }
                this._s.away = true;
                width = -(this.width() - this._s.awayWidth);
            } else {
                if (!this._s.away) {
                    this._s.awayOnBlur = false;
                    return;
                }
                this._s.away = false;
                this._s.awayOnBlur = false;
                width = this.width() - this._s.awayWidth;
            }

            $(document).trigger('MU/panels/updateSum', [width]);

            if (refresh === undefined || refresh === true) {
                $(document).trigger('MU/panels/refresh');
            }
        },

        // set panel to be full screen
        // value : boolean
        full : function (value) {
            if (value === undefined) { return this._s.full; }
            if (value === this._s.full) { return; }
            if (value) {
                this._s.full = true;
                $(document).trigger('MU/panels/focus', [this.id()]);
                this._s.oldZIndex = this.element.css('z-index');
                this.element.css('z-index', 10000);
                this.element.animate({
                    left : 0,
                    width : '100%'
                });
            } else {
                this._s.full = false;
                this.animate(function () {
                    this.element.css('z-index', this._s.oldZIndex);
                });
            }
        },

        // if insensitive, then panel cannot be focused
        // value : boolean
        insensitive : function (value) {
            if (value === undefined) { return this._s.insensitive; }
            if (value) {
               if (this.focus()) {
                    this.blur();
                    this._s.insensitive = true;
                    $(document).trigger('MU/panels/focusNext', [this.id()]);
                } else {
                    this._s.insensitive = true;
                }
            } else {
                this._s.insensitive = false;
            }
        },

        // weather panel is busy
        // value : boolean
        busy : function (value) {
            if (value === undefined) { return this._s.busy; }
            if (value) {
                if (this._s.busy) { return; }
                var busy = $('<div class="loading panel-busy" style="opacity:0;" />').prependTo(this.element);
                busy.animate({'opacity': 0.75});
                this._s.busy = true;
            } else {
                if (!this._s.busy) { return; }
                this.element.find('div.panel-busy').fadeOut(400, function () {
                    this.remove();
                });
                this._s.busy = false;
            }
        },

        // Animate all the changes made to the element.
        animate : function (callback) {
            if (this._s.closing) { return; }
            // console.log(this._s.width + this._s.expandFor);
            var _this = this;
            this.element.animate({
                left    : this._s.position + this._s.offset,
                width   : this._s.width + this._s.expandFor,
                opacity : 1
            }, 400, 'ease', function () {
                if (typeof callback === 'function') {
                    callback.call(_this);
                }
            });
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
            if (width === undefined) { return this._s.expandFor; }
            this._s.expandFor = width;
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

        // get/set panel's size by word
        // value  : string   tiny|small|medium|big
        // update : boolean  apply changes
        size : function (value) {
            if (value === undefined) { return this._s.size; }
            var sizeDiff = 0;
            // set new size by word
            this._s.size = typeof dimensions[value] === 'undefined' ? 'small' : value;
            sizeDiff = -(this._s.width - dimensions[this._s.size]);
            this._s.width = dimensions[this._s.size];
            this.animate();
            $(document).trigger('MU/panels/updateSum', [sizeDiff, this.id()]);
            $(document).trigger('MU/panels/refresh');
        },

        // get panel's size in px
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

        // take focus away from panels
        blur : function () {
            this.element.removeClass('selected');
            if (this._s.awayOnBlur) {
                this.away(true, false);
            }
        },

        // get information about focus (can't set it)
        focus : function () {
            return this.element.hasClass('selected');
        }
    };

    MU.Panel = Panel;

}(Zepto, MU));
