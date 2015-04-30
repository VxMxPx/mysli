var __extends = this.__extends || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    __.prototype = b.prototype;
    d.prototype = new __();
};
/// <reference path="widget.ts" />
/// <reference path="panel.ts" />
/// <reference path="_inc.common.ts" />
var mysli;
(function (mysli) {
    var js;
    (function (js) {
        var ui;
        (function (ui) {
            var PanelContainer = (function (_super) {
                __extends(PanelContainer, _super);
                function PanelContainer(options) {
                    if (options === void 0) { options = {}; }
                    _super.call(this, options);
                    // sum of all panels (widths, px)
                    this.sum_size = 0;
                    // number of panels which are expandable
                    this.expandable_count = 0;
                    // currently selected panel (id)
                    this.active_id = null;
                    // weather panels are offseted (overflown)
                    this.offseted = false;
                    // width of container
                    this.container_width = 0;
                    // Resize timer handle
                    this.resize_timer = null;
                    // Collection of all constructed panels
                    this.collection = new js.common.Arr();
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
                PanelContainer.prototype.update_sum = function (value, modify_before_id) {
                    this.sum_size = this.sum_size + value;
                    if (typeof modify_before_id !== 'undefined') {
                        this.collection.each_after(modify_before_id, function (index, panel) {
                            panel.element.css('z-index', 10000 - index);
                            panel.position = panel.position + value;
                            panel.animate();
                        });
                    }
                };
                /**
                 * Update view when panel is added/removed or window is resized.
                 */
                PanelContainer.prototype.update_view = function () {
                    var active_panel;
                    var overflow;
                    var overflow_part;
                    var overflow_percent;
                    var screen_left;
                    var offset_so_far;
                    var panel_calculated;
                    var diff;
                    if (!this.active_id) {
                        return;
                    }
                    active_panel = this.get(this.active_id);
                    overflow = this.container_width - this.sum_size;
                    overflow_part = this.expandable_count > 0 ? Math.ceil(overflow / this.expandable_count) : 0;
                    screen_left = this.container_width - active_panel.width;
                    overflow_percent = 100 - js.common.Num.get_percent(screen_left, this.sum_size - active_panel.width);
                    offset_so_far = 0;
                    panel_calculated = 0;
                    if (overflow_part <= 0) {
                        overflow_part = overflow;
                    }
                    if (overflow > 0) {
                        overflow_percent = 0;
                        this.offseted = false;
                    }
                    else {
                        this.offseted = true;
                    }
                    this.collection.each(function (index, panel) {
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
                            }
                            else {
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
                        panel_calculated = js.common.Num.set_percent(overflow_percent, panel.width);
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
                            }
                            else {
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
                };
                /**
                 * Push panel after particular panel of different ID.
                 */
                PanelContainer.prototype.insert = function (panel, after_id) {
                    var _this = this;
                    var index;
                    var size = 0;
                    if (typeof after_id === 'string') {
                        size = this.get(after_id).width;
                        size = typeof size === 'number' ? size : 0;
                        this.collection.each_before(after_id, function (index, ipanel) {
                            ipanel.element.css('z-index', 10000 - index);
                            size += ipanel.width;
                        });
                        panel.position = size;
                    }
                    else {
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
                            this.collection.each_after(panel.uid, function (index, ipanel) {
                                ipanel.element.css('z-index', 10000 - index);
                                ipanel.position = ipanel.position + panel.width;
                                ipanel.animate();
                            });
                        }
                    }
                    else {
                        this.collection.push(panel.uid, panel);
                    }
                    this.element.append(panel.element);
                    index = this.collection.get_index(panel.uid);
                    panel.element.css('z-index', 10000 - index);
                    // Connect panel's events
                    panel.connect('set-focus', this.switch_focus.bind(this));
                    panel.connect('set-expandable', function (status) {
                        _this.expandable_count = _this.expandable_count + (status ? 1 : -1);
                    });
                    panel.connect('close', function () {
                        _this.remove(panel.uid);
                    });
                    panel.focus = true;
                    if (panel.expandable) {
                        this.expandable_count++;
                    }
                    return panel;
                };
                /**
                 * Add a new panel to the collection.
                 */
                PanelContainer.prototype.push = function (panel) {
                    return this.insert(panel, null);
                };
                /**
                 * Get panel by id.
                 */
                PanelContainer.prototype.get = function (id) {
                    return this.collection.get(id);
                };
                /**
                 * Remove panel by id.
                 */
                PanelContainer.prototype.remove = function (id) {
                    var panel = this.get(id);
                    var width = panel.width;
                    var next_panel;
                    if (panel.expandable) {
                        this.expandable_count--;
                    }
                    this.update_sum(-(width), id);
                    if (id == this.active_id) {
                        this.active_id = null;
                        next_panel = this.collection.get_from(id, -1);
                        this.collection.remove(id);
                        next_panel.focus = true;
                    }
                    else {
                        this.collection.remove(id);
                        this.update_view();
                    }
                };
                /**
                 * Element will resize according to window resize.
                 */
                PanelContainer.prototype.set_resize_with_window = function (status, timeout) {
                    if (timeout === void 0) { timeout = 500; }
                    if (status) {
                        $(window).on('resize', function () {
                            if (this.resize_timer) {
                                clearTimeout(this.resize_timer);
                            }
                            this.resize_timer = setTimeout(this.set_size_from_dom_element.bind(this, window), timeout);
                        }.bind(this));
                    }
                    else {
                        if (this.resize_timer) {
                            clearTimeout(this.resize_timer);
                        }
                    }
                };
                /**
                 * Set element's size to DOM element's size.
                 */
                PanelContainer.prototype.set_size_from_dom_element = function (selector) {
                    var width = $(selector).outerWidth();
                    var height = $(selector).outerHeight();
                    this.element.css({
                        width: width,
                        height: height
                    });
                    this.container_width = width;
                    this.update_view();
                };
                /**
                 * Remove old focus, and set new
                 */
                PanelContainer.prototype.switch_focus = function (status, panel) {
                    if (status) {
                        if (this.active_id) {
                            this.get(this.active_id).focus = false;
                        }
                        this.active_id = panel.uid;
                        this.update_view();
                    }
                };
                return PanelContainer;
            })(ui.Widget);
            ui.PanelContainer = PanelContainer;
        })(ui = js.ui || (js.ui = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
