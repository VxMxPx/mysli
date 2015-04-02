mysli.web.ui.panel_container = (function () {

    'use strict';

    var ui = mysli.web.ui,
        template = '<div class="ui-widget ui-panel-container" />';

    var panel_container = function () {
        this.$element = $(template);
        this.uid = ui._.uid(this.$element);

        // sum of all panels (widths, px)
        this.sum_size = 0;
        // number of panels which are expandable
        this.expandable_count = 0;
        // currently selected panel (id)
        this.active_id = false;
        // weather panels are offseted (overflown)
        this.offseted = 0;
        // width of container
        this.container_width = 0;
        // collection of all panels
        this.panels = new ui._.arr();

        this.resize_timer = null;

        this.set_resize_with_window(true);
        this.set_size_from_dom_element(window);
    };

    panel_container.prototype = {

        constructor : panel_container,

        /**
         * Default event
         */
        event: ui._.extend.event(['<native>']),

        /**
         * Update sum size, when panel is added, removed or away.
         * @param  {Integer} value positive or negative
         * @param  {String}  modify_before_id if provided, panels before an id
         * will update position to fit difference.
         */
        update_sum: function (value, modify_before_id) {
            this.sum_size = this.sum_size + value;
            if (typeof modify_before_id !== 'undefined') {
                this.panels.each_after(modify_before_id, function (index, panel) {
                    panel.$element.css('z-index', 10000 - index);
                    panel._.position = panel._.position + value;
                    panel.animate();
                    return;
                });
            }
        },

        /**
         * Update view when panel is added/removed or window is resized.
         */
        update_view: function () {
            var active_panel, overflow, overflow_part, overflow_percent,
                screen_left, offset_so_far, panel_calculated, diff;

            if (!this.active_id) {
                return;
            }

            active_panel = this.get(this.active_id);
            // # if active_panel.size().width > @container_width
            // #     active_panel.size('small')

            overflow = this.container_width - this.sum_size;
            overflow_part = this.expandable_count > 0 ? Math.ceil(overflow / this.expandable_count) : 0;

            screen_left = this.container_width - active_panel.size().width;
            overflow_percent = 100 - ui._.get_percent(screen_left, this.sum_size - active_panel.size().width);
            offset_so_far = 0;
            panel_calculated = 0;

            if (overflow_part <= 0) {
                overflow_part = overflow;
            }

            if (overflow > 0) {
                overflow_percent = 0;
                this.offseted = false;
            } else {
                this.offseted = true;
            }

            this.panels.each(function (index, panel) {

                if (panel.away() && !panel.focus()) {
                    panel._.expanded_for = 0;
                    panel._.offset = -(panel.size().width - panel._.away_width + offset_so_far);
                    panel.animate();
                    offset_so_far = offset_so_far + panel.size().width - panel._.away_width;
                    return;
                }

                if (panel.expandable()) {
                    if (overflow > 0) {
                        panel._.offset = -(offset_so_far);
                        panel._.expanded_for = overflow_part;
                        panel.animate();
                        offset_so_far += -(overflow_part);
                        return;
                    } else {
                        panel._.expanded_for = 0;
                        panel.animate();
                    }
                }

                if (panel.focus()) {
                    panel._.expanded_for = 0;
                    panel._.offset = -(offset_so_far);
                    panel.animate();
                    return;
                }

                // # panel_calculated = Math.ceil(MU.Calc.set_percent(overflow_percent, panel.properties.width))
                panel_calculated = ui._.set_percent(overflow_percent, panel.size().width);

                // is shrinkable and still can be shrinked
                if (panel.min_size() && panel.size().width + panel._.expanded_for > panel.min_size()) {
                    // can whole offset be shrinked?
                    if (panel.min_size() > panel.size().width - panel_calculated) {
                        diff = panel_calculated - (panel.size().width - panel.min_size());
                        panel._.expanded_for = -(diff);
                        panel._.offset = -(panel_calculated - diff + offset_so_far);
                        panel.animate();
                        offset_so_far += panel_calculated;
                        return;
                    } else {
                        panel._.expanded_for = -(panel_calculated);
                        panel._.offset = -(offset_so_far);
                        panel.animate();
                        offset_so_far += panel_calculated;
                        return;
                    }
                }

                panel._.expanded_for = 0;
                panel._.offset = -(panel_calculated + offset_so_far);
                panel.animate();
                offset_so_far += panel_calculated;
                return;
            });
        },


        /**
         * Push panel after particular panel of different ID.
         * @param  {String} panel
         * @param  {String} after_id
         */
        push_after: function (panel, after_id) {
            var index;

            if (!(panel instanceof mysli.web.ui.panel)) {
                throw new Error('An object must be instance of `mysli.web.ui.panel`');
            }

            if (typeof after_id === 'string') {
                size = this.get(after_id).size().width;
                this.panels.each_before(after_id, function (index, ipanel) {
                    ipanel.$element.css('z-index', 10000 - index);
                    size += ipanel.size().width;
                    return;
                });
                panel._.position = size;
            } else {
                panel._.position = this.sum_size;
            }

            this.update_sum(panel.size().width);

            panel.$element.css({
                opacity: 0,
                left: (panel._.position + panel._.offset) - (panel.size().width + panel._.expanded_for)
            });

            if (after_id) {
                this.panels.push_after(after_id, panel.uid, panel);
                if (panel.uid !== this.panels.get_last().uid) {
                    this.panels.each_after(panel.uid, function (index, ipanel) {
                        ipanel.$element.css('z-index', 10000 - index);
                        ipanel._.position = ipanel._.position + panel.size().width;
                        ipanel.animate();
                        return;
                    });
                }
            } else {
                this.panels.push(panel.uid, panel);
            }

            index = this.panels.get_index(panel.uid);
            panel.$element.css('z-index', 10000 - index);
            panel.connect('focus-change', this.switch_focus.bind(this));
            // # panel.connect 'size-change', (size, panel) =>
            // #     @update_sum size.diff, panel.get_id()

            panel.focus(true);

            if (panel.expandable()) {
                this.expandable_count++;
            }
        },

        /**
         * Add a new panel to the collection.
         * @param {Object} panel
         */
        push: function (panel) {
            return this.push_after(panel, false, cellid);
        },

        /**
         * Get panel by id.
         * @param  {String} id
         * @return {Object}
         */
        get: function (id) {
            return this.panels.get(id);
        },

        /**
         * Remove panel by id.
         * @param  {String} id
         */
        remove: function (id) {
            var panel = this.get(id),
                width = panel.size().width,
                new_panel;

            if (panel.expandable()) {
                this.expandable_count--;
            }

            this.update_sum(-(width), id);

            if (id == this.active_id) {
                this.active_panel = false;
                new_panel = this.panels.get_from(id, -1);
                this.panels.remove(id);
                new_panel.focus(true);
            } else {
                this.panels.remove(id);
                this.update_view();
            }
        },

        /**
         * Element will resize according to window resize.
         * @param {Boolean} status
         * @param {Integer} timeout
         */
        set_resize_with_window: function (status, timeout) {
            if (typeof timeout === 'undefined') {
                timeout = 500;
            }

            if (status) {
                $(window).on('resize', function () {
                    if (this.resize_timer) {
                        clearTimeout(this.resize_timer);
                    }
                    this.resize_timer = setTimeout(this.set_size_from_dom_element.bind(this, window), timeout);
                }.bind(this));
            } else {
                if (this.resize_timer) {
                    clearTimeout(this.resize_timer);
                }
            }
        },

        /**
         * Set element's size to DOM element's size.
         * @param {String} selector
         */
        set_size_from_dom_element: function (selector) {
            var width = $(selector).outerWidth(),
                height = $(selector).outerHeight();

            this.$element.css({
                width: width,
                height: height
            });

            this.container_width = width;
            this.update_view();
        },

        /**
         * Remove old focus, and set new
         * @param {Boolean} status
         * @param {Object}  panel
         */
        switch_focus: function (status, panel) {
            if (status) {
                if (this.active_id !== false) {
                    this.get(this.active_id).focus(false);
                }
                this.active_id = panel.uid;
                this.update_view();
            }
        }
    };

    return panel_container;

}());
