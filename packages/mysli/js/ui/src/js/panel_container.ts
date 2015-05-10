/// <reference path="widget.ts" />
/// <reference path="panel.ts" />
/// <reference path="_inc.common.ts" />
module mysli.js.ui {
    export class PanelContainer extends Widget {

        // sum of all panels (widths, px)
        private sum_size: number = 0;
        // number of panels which are expandable
        private expandable_count: number = 0;
        // currently selected panel (id)
        private active_id: string = null;
        // weather panels are offseted (overflown)
        private offseted: boolean = false;
        // width of container
        private container_width: number = 0;
        // Resize timer handle
        private resize_timer: any = null;
        // Collection of all constructed panels
        private collection: common.Arr = new common.Arr();

        constructor(options: any = {}) {

            super(options);

            this.element.addClass('ui-panel-container');

            this.set_resize_with_window(true);
            this.set_size_from_dom_element(window);
        }

        /**
         * Update sum size, when panel is added, removed or away.
         * @param value can be either positive or negative
         * @param modify_before_id, if provided, panels before an id
         * will update position to fit difference.
         */
        update_sum(value: number, modify_before_id?: string): void {
            this.sum_size = this.sum_size + value;

            if (typeof modify_before_id !== 'undefined') {
                this.collection.each_after(modify_before_id, function (index: number, panel: Panel) {
                    panel.element.css('z-index', 10000 - index);
                    panel.position = panel.position + value;
                    panel.animate();
                });
            }
        }

        /**
         * Update view when panel is added/removed or window is resized.
         */
        update_view(): void {
            var active_panel: Panel;
            var overflow: number;
            var overflow_part: number;
            var overflow_percent: number;
            var screen_left: number;
            var offset_so_far: number;
            var panel_calculated: number;
            var diff: number;

            if (!this.active_id) {
                return;
            }

            active_panel = this.get(this.active_id);

            if (active_panel.width > this.container_width) {
                this.sum_size = this.sum_size + (this.container_width - active_panel.width)
                active_panel.width = this.container_width;
            }

            overflow = this.container_width - this.sum_size;
            overflow_part = this.expandable_count > 0 ? Math.floor(overflow / this.expandable_count) : 0;
            screen_left = this.container_width - active_panel.width;
            overflow_percent = 100 - common.Num.get_percent(screen_left, this.sum_size - active_panel.width);
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

            this.collection.each(function (index: number, panel: Panel) {
                if (panel.away && !panel.focus) {
                    panel.expand = 0;
                    panel.offset = -(panel.width - panel.away_width + offset_so_far);
                    panel.animate();
                    offset_so_far = offset_so_far + panel.width - panel.away_width;
                    return;
                }

                if (panel.expandable) {
                    if (overflow > 0) {
                        panel.offset = -(offset_so_far);
                        panel.expand = overflow_part;
                        panel.animate();
                        offset_so_far += -(overflow_part);
                        return;
                    } else {
                        panel.expand = 0;
                        panel.animate();
                    }
                }

                if (panel.focus) {
                    panel.expand = 0;
                    panel.offset = -(offset_so_far);
                    panel.animate();
                    return;
                }

                // # panel_calculated = Math.ceil(MU.Calc.set_percent(overflow_percent, panel.properties.width))
                panel_calculated = common.Num.set_percent(overflow_percent, panel.width);

                // is shrinkable and still can be shrinked
                if (panel.min_size && panel.width + panel.expand > panel.min_size) {
                    // can whole offset be shrinked?
                    if (panel.min_size > panel.width - panel_calculated) {
                        diff = panel_calculated - (panel.width - panel.min_size);
                        panel.expand = -(diff);
                        panel.offset = -(panel_calculated - diff + offset_so_far);
                        panel.animate();
                        offset_so_far += panel_calculated;
                        return;
                    } else {
                        panel.expand = -(panel_calculated);
                        panel.offset = -(offset_so_far);
                        panel.animate();
                        offset_so_far += panel_calculated;
                        return;
                    }
                }

                panel.expand = 0;
                panel.offset = -(panel_calculated + offset_so_far);
                panel.animate();
                offset_so_far += panel_calculated;
            });
        }

        /**
         * Push panel after particular panel of different ID.
         */
        insert(panel: Panel, after_id: string): Panel {
            let index: number;
            var size: number = 0;

            if (typeof after_id === 'string') {
                size = this.get(after_id).width;
                size = typeof size === 'number' ? size : 0;

                this.collection.each_before(after_id, function (index: number, ipanel: Panel) {
                    ipanel.element.css('z-index', 10000 - index);
                    size += ipanel.width;
                });
                panel.position = size;
            } else {
                panel.position = this.sum_size;
            }

            this.update_sum(panel.width);

            panel.element.css({
                opacity: 0,
                left: (panel.position + panel.offset) - (panel.width + panel.expand)
            });

            if (after_id) {
                this.collection.push_after(after_id, panel.uid, panel);
                if (panel.uid !== this.collection.get_last().uid) {
                    this.collection.each_after(panel.uid, function (index: number, ipanel: Panel) {
                        ipanel.element.css('z-index', 10000 - index);
                        ipanel.position = ipanel.position + panel.width;
                        ipanel.animate();
                    });
                }
            } else {
                this.collection.push(panel.uid, panel);
            }

            this.element.append(panel.element);

            index = this.collection.get_index(panel.uid);
            panel.element.css('z-index', 10000 - index);

            // Connect panel's events
            panel.connect('set-focus', this.switch_focus.bind(this));
            panel.connect('set-expandable', (status: boolean) => {
                this.expandable_count = this.expandable_count + (status ? 1 : -1);
            });
            panel.connect('close', () => {
                this.remove(panel.uid);
            });

            panel.focus = true;

            if (panel.expandable) {
                this.expandable_count++;
            }

            return panel;
        }

        /**
         * Add a new panel to the collection.
         */
        push(panel: Panel): Panel {
            return this.insert(panel, null);
        }

        /**
         * Get panel by id.
         */
        get(id:string): Panel {
            return this.collection.get(id);
        }

        /**
         * Remove panel by id.
         */
        remove(id: string): void {
            var panel: Panel = this.get(id);
            var width = panel.width;
            var next_panel: Panel;

            if (panel.expandable) {
                this.expandable_count--;
            }

            this.update_sum(-(width), id);

            if (id == this.active_id) {
                this.active_id = null;
                next_panel = this.collection.get_from(id, -1);
                this.collection.remove(id);
                next_panel.focus = true;
            } else {
                this.collection.remove(id);
                this.update_view();
            }
        }

        /**
         * Element will resize according to window resize.
         */
        set_resize_with_window(status: boolean, timeout: number = 500): void {
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
        }

        /**
         * Set element's size to DOM element's size.
         */
        set_size_from_dom_element(selector: any): void {

            var width: number = $(selector).outerWidth();
            var height: number = $(selector).outerHeight();

            this.element.css({
                width: width,
                height: height
            });

            this.container_width = width;
            this.update_view();
        }

        /**
         * Remove old focus, and set new
         */
        switch_focus(status: boolean, panel: Panel): void {
            if (status) {
                if (this.active_id) {
                    this.get(this.active_id).focus = false;
                }
                this.active_id = panel.uid;
                this.update_view();
            }
        }

    }
}
