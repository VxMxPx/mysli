;(function ($, MU) {

    // Number of initialized overlays, used to generate unique IDs
    var count = 0;

    // Parent's geometry + applied padding
    // return : object {top: <int>, left: <int>, width: <int>, height: <int>}
    function get_geometery(parent, padding) {
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
    // can_close : boolean  If true, click on overlay will hide it.
    // id        : string   Unique element ID (will be auto-generated if not provided).
    // classes   : array    List of classes.
    //
    var Overlay = function (parent, options) {

        var _this = this;

        this.options = $.extend({}, {
            loading     : false,
            text        : null,
            padding     : 0,
            can_close   : false,
            on_click    : false,
            id          : null,
            classes     : []
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
            this.options.id = 'mu_overlay_' + count;
        }

        this.$element.attr('id', this.options.id);

        if (this.options.classes.length)
            this.$element.addClass(this.options.classes.join(' '));

        if (this.options.can_close)
            this.$element.on('click', _this.hide);

        this.is_visible   = false;
        this.is_appended  = false;
        this.resize       = {
            timer : false,
            event : false
        };

        // Increase count of initialized objects by one (Used for ID)
        count = count + 1;
    }

    Overlay.prototype = {

        constructor : Overlay,

        // callback : callback Function of false to remove event.
        // return   : this
        on_click : function (callback) {
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
                geometry = get_geometery(this.$parent, this.options.padding);

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

            var _this = this;

            // If parent is not visible, overlay won't be displayed either.
            if (this.$parent && !this.$parent.is(':visible')) return;

            // Is overlay already displayed?
            if (this.is_visible) return;

            if (!geometry && this.$parent)
                geometry = get_geometery(this.$parent, this.options.padding);

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
                    if (_this.resize.timer)
                        clearTimeout(_this.resize.timer);
                    _this.resize.timer = setTimeout(function () {
                        _this.update(false);
                        console.log('Fire!!');
                    }, 200);
                });
            }

            if (typeof this.options.on_click === 'function') {
                this.on_click = this.options.on_click;
            }

            this.is_visible = true;

            if (!this.is_appended) {
                this.$element.appendTo('body');
                this.is_appended = true;
            }

            this.$element.fadeIn();
        },

        // Hide the overlay.
        // return : null
        hide : function () {
            this.is_visible = false;
            this.$element.fadeOut();
            if (this.resize.event) {
                $(window).off(this.resize.event);
                this.resize.event = false;
            }
        }
    };

    MU.Overlay = Overlay;

}(Zepto, MU));
