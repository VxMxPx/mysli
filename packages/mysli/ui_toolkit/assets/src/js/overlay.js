;(function ($, MU) {

    'use strict';

    // Number of initialized overlays, used to generate unique IDs
    var count = 0;

    // Parent's geometry + applied padding
    // return : object {top: <int>, left: <int>, width: <int>, height: <int>}
    function getGeometery(parent, padding) {
        return {
            top:     parent.offset().top  - padding,
            left:    parent.offset().left - padding,
            width:   parent.width()  + (padding * 2),
            height:  parent.height() + (padding * 2)
        };
    }

    // Options:
    // loading   : boolean  Loading indicator will be displayed
    // text      : string   Text will be displayed
    // padding   : integer  When appended to the element, padding will be applied,
    //                      meaning that overlay will expand over element.
    // canClose  : boolean  If true, click on overlay will hide it.
    // id        : string   Unique element ID (will be auto-generated if not provided).
    // classes   : array    List of classes.
    //
    var Overlay = function (parent, options) {

        var that = this;

        this.options = $.extend({}, {
            loading  : false,
            text     : null,
            padding  : 0,
            canClose : false,
            onClick  : false,
            id       : null,
            classes  : []
        }, options);

        this.$element = $('<div class="overlay" />');

        if (!parent) {
            this.options.classes.push('expanded');
            this.$parent = false;
        } else this.$parent = $(parent);

        if (this.options.text)
            this.$element.append('<div class=text>' + this.options.text + '</div>');

        if (this.options.loading) {
            this.options.classes.push('loading');
            this.$element.append('<i class="fa spinner fa-spinner fa-spin"></i>');
        }

        if (!this.options.id) {
            this.options.id = 'mu-overlay-' + count;
        }

        this.$element.attr('id', this.options.id);

        if (this.options.classes.length)
            this.$element.addClass(this.options.classes.join(' '));

        if (this.options.canClose)
            this.$element.on('click', that.hide);

        this.isVisible   = false;
        this.isAppended  = false;
        this.resize       = {
            timer : false,
            event : false
        };

        // Increase count of initialized objects by one (Used for ID)
        count = count + 1;
    };

    Overlay.prototype = {

        constructor : Overlay,

        // callback : callback Function of false to remove event.
        // return   : this
        onClick : function (callback) {
            if (typeof callback === 'function')
                this.$element.on('click.callback', callback);
            else
                this.$element.unbind('click.callback');
            return this;
        },

        // Update overlay dimension, taken from parent element.
        // animated : boolean Should dimension change be animated?
        // geometry : object {top: <int>, left: <int>, width: <int>, height: <int>}
        // return   : null
        update : function (animated, geometry) {
            if (!geometry && this.$parent)
                geometry = getGeometery(this.$parent, this.options.padding);

            if (typeof geometry === 'object') {
                this.$element[animated ? 'animate' : 'css']({
                    top      : geometry.top,
                    left     : geometry.left,
                    width    : geometry.width,
                    height   : geometry.height
                });
            }
        },

        // Display overlay if not already visible
        // geometry : object  {top: <int>, left: <int>, width: <int>, height: <int>}
        // return   : null
        show : function (geometry) {

            var that = this;

            // If parent is not visible, overlay won't be displayed either.
            if (this.$parent && !this.$parent.is(':visible')) return;

            // Is overlay already displayed?
            if (this.isVisible) return;

            if (!geometry && this.$parent)
                geometry = getGeometery(this.$parent, this.options.padding);

            if (typeof geometry === 'object') {
                this.$element.css({
                    display  : 'none',
                    top      : geometry.top,
                    left     : geometry.left,
                    width    : geometry.width,
                    height   : geometry.height,
                    position : 'absolute'
                });
            }

            // Add resize event
            if (this.$parent) {
                this.resize.event = $(window).on('resize', function () {
                    if (that.resize.timer)
                        clearTimeout(that.resize.timer);
                    that.resize.timer = setTimeout(function () {
                        that.update(false);
                        console.log('Fire!!');
                    }, 200);
                });
            }

            if (typeof this.options.onClick === 'function') {
                this.onClick = this.options.onClick;
            }

            this.isVisible = true;

            if (!this.isAppended) {
                this.$element.appendTo('body');
                this.isAppended = true;
            }

            this.$element.fadeIn();
        },

        // Hide the overlay.
        // return : null
        hide : function () {
            this.isVisible = false;
            this.$element.fadeOut();
            if (this.resize.event) {
                $(window).off(this.resize.event);
                this.resize.event = false;
            }
        }
    };

    MU.Overlay = Overlay;

}(Zepto, MU));
