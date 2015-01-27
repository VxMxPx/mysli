// Generated by CoffeeScript 1.8.0
(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  mysli.web.ui.panel = (function(_super) {
    var dimensions, template, ui;

    __extends(panel, _super);

    ui = mysli.web.ui;

    template = '<div class="ui-panel ui-widget" />';

    dimensions = {
      tiny: 160,
      small: 260,
      "default": 340,
      big: 500,
      huge: 800
    };

    function panel(id, options) {
      if (options == null) {
        options = {};
      }
      if (!id) {
        throw new Error("Panel's ID is required");
      }
      panel.__super__.constructor.call(this, options);
      this.elements.push($(template));
      this.events['away-change'] = {};
      this.events['size-change'] = {};
      this.events['popout-change'] = {};
      this.events['insensitive-change'] = {};
      this.events['min-size-change'] = {};
      this.events['focus-change'] = {};
      this.events['expandable-change'] = {};
      this.connected_panels = [];
      this.properties = ui.util.merge_options(options, {
        position: 0,
        offset: 0,
        locked: false,
        expandable: false,
        expanded_for: 0,
        min_size: false,
        size: 'default',
        width: 0,
        away: false,
        away_on_blur: false,
        away_width: 10,
        insensitive: false,
        popout: false,
        focused: false,
        flippable: false
      });
      this.closing = false;
      this.old_zindex = 0;
      this.old_width = 0;
      this.set_id(id);
      ui.util.apply_options(this.properties, this, {
        set_min_size: 'min_size',
        set_size: 'size'
      });
      this.connect('click', this.set_focus.bind(this, true));
      this.front = new ui.panel_side('front');
      this.get_element().append(this.front.get_element());
      if (this.properties.flippable) {
        this.back = new ui.panel_side('back');
        this.get_element().append(this.back.get_element());
      } else {
        this.back = false;
      }
    }


    /*
    Animate all the changes made to the element.
     */

    panel.prototype.animate = function(callback) {
      if (this.closing) {
        return;
      }
      return this.get_element().stop(true, false).animate({
        left: this.properties.position + this.properties.offset,
        width: this.properties.width + this.properties.expanded_for,
        opacity: 1
      }, 400, 'swing', (function(_this) {
        return function() {
          if (typeof callback === 'function') {
            return callback.call(_this);
          }
        };
      })(this));
    };


    /*
    Set size by word. Please see dimensions for available sizes
    @param {string} size
     */

    panel.prototype.set_size = function(size) {
      var size_diff;
      size_diff = 0;
      if (typeof dimensions[size] === 'undefined') {
        throw new Error("Invalid value for size: `" + size + "`");
      }
      this.properties.size = size;
      size_diff = -(this.properties.width - dimensions[size]);
      this.properties.width = dimensions[size];
      return this.trigger('size-change', [
        {
          width: this.properties.width,
          height: 0,
          size: size,
          diff: size_diff
        }
      ]);
    };


    /*
    Get panel's size
    @returns {object} {width: int, height: int, size: string}
     */

    panel.prototype.get_size = function() {
      return {
        width: this.properties.width,
        height: 0,
        size: this.properties.size
      };
    };


    /*
    Set panel's away status.
    @param {boolean} status
     */

    panel.prototype.set_away = function(status) {
      var width;
      if (status) {
        if (this.get_focus() || this.properties.away) {
          this.properties.away_on_blur = true;
          return;
        }
        this.properties.away = true;
        width = -(this.properties.width - this.properties.away_width);
      } else {
        if (!this.properties.away) {
          this.away_on_blur = false;
          return;
        }
        this.properties.away = false;
        this.properties.away_on_blur = false;
        width = this.properties.width - this.properties.away_width;
      }
      return this.trigger('away-change', [status, width]);
    };


    /*
    Get panel's away status
    @returns {boolean}
     */

    panel.prototype.get_away = function() {
      return this.properties.away;
    };


    /*
    Set panel's popout status
    @param {boolean} value
     */

    panel.prototype.set_popout = function(value, size) {
      if (size == null) {
        size = 'huge';
      }
      if (value === this.get_popout) {
        return;
      }
      if (value) {
        this.properties.popout = true;
        this.set_focus(true);
        this.old_zindex = this.get_element().css('z-index');
        this.old_width = this.properties.width;
        this.get_element().css('z-index', 10005);
        this.properties.width = dimensions[size];
        this.animate;
      } else {
        this.properties.popout = false;
        this.get_element().css('z-index', this.old_zindex);
        this.properties.width = this.old_width;
      }
      return this.trigger('popout-change', [value]);
    };


    /*
    Get popout status
    @returns {boolean}
     */

    panel.prototype.get_popout = function() {
      return this.properties.popout;
    };


    /*
    Set insensitive status
    @param {boolean} value
     */

    panel.prototype.set_insensitive = function(value) {
      if (value) {
        if (this.get_focus()) {
          this.set_focus(false);
        }
        this.properties.insensitive = true;
      } else {
        this.properties.insensitive = false;
      }
      return this.trigger('insensitive-change', [value]);
    };


    /*
    Get insensitive status
    @returns {boolean}
     */

    panel.prototype.get_insensitive = function() {
      return this.properties.insensitive;
    };


    /*
    Set panel's miz size
    @param {string} size
     */

    panel.prototype.set_min_size = function(size) {
      if (size && typeof dimensions[size] === 'undefined') {
        throw new Error("Not a valid size value: `" + size + "`");
      }
      this.properties.min_size = size ? dimensions[size] : false;
      return this.trigger('min-size-change', [size, dimensions[size]]);
    };


    /*
    Get panel's min size
    @returns {integer}
     */

    panel.prototype.get_min_size = function() {
      return this.properties.min_size;
    };


    /*
    Set panel's focus
    @param {boolean} value
     */

    panel.prototype.set_focus = function(value) {
      if (value === this.properties.focused) {
        return;
      }
      if (value) {
        this.properties.focused = true;
        this.get_element().addClass('focused');
        if (this.properties.away) {
          this.set_away(false);
          this.properties.away_on_blur = true;
        }
      } else {
        this.properties.focused = false;
        this.get_element().removeClass('focused');
        if (this.properties.away_on_blur) {
          this.set_away(true);
        }
      }
      return this.trigger('focus-change', [value]);
    };


    /*
    Get current focus status
    @returns {boolean}
     */

    panel.prototype.get_focus = function() {
      return this.properties.focused;
    };


    /*
    Set expandable status
    @param {boolean} value
     */

    panel.prototype.set_expandable = function(value) {
      this.properties.expandable = value;
      return this.trigger('expandable-change', [value]);
    };


    /*
    Get expandable status
    @returns {boolean}
     */

    panel.prototype.get_expandable = function() {
      return this.properties.expandable;
    };


    /*
    Destroy/remove this panel
     */

    panel.prototype.destroy = function() {
      if (this.locked) {
        return;
      }
      this.insensitive = true;
      this.closing = true;
      return this.get_element().stop(true, false).animate({
        left: (this.properties.position + this.properties.offset) - (this.get_size().width + this.properties.expanded_for) - 10,
        opacity: 0
      }, 400, 'swing', (function(_this) {
        return function() {
          return panel.__super__.destroy.apply(_this, arguments);
        };
      })(this));
    };

    return panel;

  })(mysli.web.ui.widget);

}).call(this);