mysli.js.ui.panel = (function () {

    'use strict';

    var ui = mysli.js.ui,
        common = mysli.js.common,
        template = '<div class="ui-panel ui-widget" />',
        dimensions = {
            tiny:   160,
            small:  260,
            normal: 340,
            big:    500,
            huge:   800
        };

    var panel = function (options) {

        // Define element and uid
        this.$element = $(template);
        this.uid = options.uid ? options.uid : ui.util.uid(this.$element);

        // Repository of connected panels
        this.connected = [];

        // Properties
        this._ = common.merge({
            // position in px from left
            position: 0,
            // when there's a lot of panels, they start being pushed aside
            // and partly hidden
            offset: 0,
            // weather panel is locked
            locked: false,
            // weather panel can be expanded to fill the available space
            expandable: false,
            // how much panel's width was increased (only if expandable is true)
            expanded_for: 0,
            // for how much can panel shrink (if 0 it can't shrink)
            min_size: false,
            // panel size by word
            size: 'normal',
            // panel's size by px
            width: 0,
            // is panel in away mode
            away: false,
            // if away on blur, then panel will go away when lose focus
            away_on_blur: false,
            // the width (px) of panel when away
            away_width: 10,
            // if insensitive, then panel cannot be focused
            insensitive: false,
            // if panel is popout
            popout: false,
            // Weather panel is in focus
            focused: false,
            // Weather panel can be flipped (back side exists!)
            flippable: false
        }, options);

        // when true, some events will be prevented on the panel,
        // like further animations
        this.closing = false;

        // when panel goes to full screen highest zIndex is set, this is the
        // original zIndex, to be restored, when full screen is turned off
        this.old_zindex = 0;
        this.old_width  = 0;

        // Apply default settings
        ui.util.use(this._, this, {
            min_size: 'min_size',
            size: 'size'
        });

        // Proxy the click event to focus
        this.$element.on('click', this.focus.bind(this, true));

        // Add Sides
        this.front = new ui.panel_side();
        this.$element.append(this.front.$element);

        if (this._.flippable) {
            this.back = new ui.panel_side({style: 'alt'});
            this.$element.append(this.back.$element);
        } else {
            this.back = false;
        }
    };

    panel.prototype = {

        constructor: panel,

        /**
         * Std. events
         */
        event: ui.extend.event([
            // Standard events (click, ...)
            '<native>',
            // On away status change
            // => ( boolean status, integer width, object this )
            'away-change',
            // On size changed
            // => ( object size {width: int, height: int, size: str, diff: int}, boolean element_exists, object this )
            'size-change',
            // On popout status changed
            // => ( boolean value, object this )
            'popout-change',
            // On insensitive status changed
            // => ( boolean value, object this )
            'insensitive-change',
            // On min_size value changed
            // => ( string size, integer size, object this )
            'min-size-change',
            // On focus changed
            // => ( boolean value, object this )
            'focus-change',
            // On expandable status changed
            // => ( boolean value, object this )
            'expandable-change'
        ]),

        /**
         * Animate all the changes made to the element.
         * @param  {Function} callback
         */
        animate: function (callback) {
            if (this.closing) {
                return;
            }

            this.$element.stop(true, false).animate({
                left: this._.position + this._.offset,
                width: this._.width + this._.expanded_for,
                opacity: 1
            }, 400, 'swing', function () {
                if (typeof callback == 'function') {
                    callback.call(this);
                }
            });
        },

        /**
         * Get/set size by word.
         * @param  {string} value
         * @return {string}
         */
        size: function (value) {
            if (typeof value !== 'undefined') {
                var diff = 0,
                    newsize = {};

                if (typeof dimensions[value] === 'undefined') {
                    throw new Error("Invalid value for size: "+value);
                }

                this._.size = value;
                diff = -(this._.width - dimensions[value]);
                this._.width = dimensions[value];

                newsize = this.size();
                newsize.diff = diff;

                this.event.trigger('size-change', [newsize]);
            } else {
                return {
                    width:  this._.width,
                    height: 0,
                    size:   this._.size
                };
            }
        },

        /**
         * Get/set away status for panel.
         * @param  {boolean} status
         * @return {boolean}
         */
        away: function (status) {
            if (typeof status !== 'undefined') {
                var width = null;
                if (status) {
                    if (this.focus() || this.away()) {
                        this._.away_on_blur = true;
                        return;
                    }
                    this._.away = true;
                    width = -(this._.width - this._.away_width);
                } else {
                    if (!this._.away) {
                        this._.away_on_blur = false;
                        return;
                    }
                    this._.away = false;
                    this.away_on_blur = false;
                    width = this._.width - this._.away_width;
                }
                this.event.trigger('away-change', [status, width]);
            } else {
                return this._.away;
            }
        },

        /**
         * Get/set panel's popout status.
         * @param  {boolean} status
         * @param  {string}  size
         * @return {boolean}
         */
        popout: function(status, size) {
            if (typeof status !== 'undefined') {
                if (status === this.popout()) {
                    return;
                }
                if (status) {
                    if (typeof size === 'undefined') {
                        size = 'huge';
                    }
                    this._.popout = true;
                    this.focus(true);
                    this.old_zindex = this.$element.css('z-index');
                    this.old_width = this._.width;
                    this.$element.css('z-index', 10005);
                    this._.width = dimensions[size];
                    this.animate();
                } else {
                    this._.popout = false;
                    this.$element.css('z-index', this.old_zindex);
                    this._.width = this.old_width;
                }
                this.event.trigger('popout-change', [status]);
            } else {
                return this._.popout;
            }
        },

        /**
         * Get/get insensitive status.
         * @param   {boolean} value
         * @returns {boolean}
         */
        insensitive: function (value) {
            if (typeof value !== 'undefined') {
                if (value) {
                    if (this.focus()) {
                        this.focus(false);
                    }
                    this._.insensitive = true;
                } else {
                    this._.insensitive = false;
                }
                this.event.trigger('insensitive-change', [value]);
            } else {
                return this._.insensitive;
            }
        },

        /**
         * Get/set panel's min size.
         * @param  {string} size
         * @return {integer}
         */
        min_size: function(size) {
            if (typeof size !== 'undefined') {
                if (size && typeof dimensions[size] === 'undefined') {
                    throw new Error("Not a valid size value: "+size);
                }
                this._.min_size = size ? dimensions[size] : false;
                this.event.trigger('min-size-change', [size, dimensions[size]]);
            } else {
                return this._.min_size;
            }
        },

        /**
         * Get/set focus.
         * @param  {boolean} value
         * @return {boolean}
         */
        focus: function (value) {
            if (typeof value !== 'undefined') {
                if (value == this._.focused) {
                    return;
                }
                if (value) {
                    this._.focused = true;
                    this.$element.addClass('focused');
                    if (this.away()) {
                        this.away(false);
                        this._.away_on_blur = true;
                    }
                } else {
                    this._.focused = false;
                    this.$element.removeClass('focused');
                    if (this._.away_on_blur) {
                        this.away(true);
                    }
                }
                this.event.trigger('focus-change', [value]);
            } else {
                return this._.focused;
            }
        },

        /**
         * Get/set expandable status.
         * @param  {boolean} value
         * @return {boolean}
         */
        expandable: function (value) {
            if (typeof value !== 'undefined') {
                this._.expandable = value;
                this.event.trigger('expandable-change', [value]);
            } else {
                return this._.expandable;
            }
        },

        /**
         * Close the panel.
         */
        close: function () {
            if (this._.locked) {
                return;
            }

            this._.insensitive = true;
            this._.closing = true;

            this.$element.stop(true, false).animate({
                left: (this._.position + this._.offset) - (this.size().width + this._.expanded_for) - 10,
                opacity: 0
            }, 400, 'swing');
        }
    };

    return panel;

}());
