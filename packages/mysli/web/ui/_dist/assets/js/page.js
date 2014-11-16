mysli.web.ui.page = (function () {

    'use strict';

    var ui = mysli.web.ui,
        template = '<div class="ui-page"></div>';

    function set_initial_size(element) {
        element.set_size({
            width:  $(window).outerWidth(),
            height: $(window).outerHeight()
        });
    }

    var self = function () {
        ui.mixins.container.call(self.prototype);
        this.elements.push($(template));
        this.parent = $('body');
        this.resize_timer = false;

        this.elements[0].css({top: 0, left: 0, position: 'relative'});
        this.parent.css('overflow', 'hidden');
        set_initial_size(this);
    };

    self.prototype = {

        constructor : self,

        /// Show (append) page element (and all containing elements)
        show : function () {
            this.parent.append(this.elements[0]);
        },

        /// Weather page should track resize event(s)
        /// @param {boolean} status
        /// @param {integer} timeout
        set_track_resize : function (status, timeout) {
            if (status) {

                timeout = timeout || 500;

                $(window).on('resize', function () {
                    if (this.resize_timer) {
                        clearTimeout(this.resize_timer);
                    }
                    this.resize_timer = setTimeout(
                        set_initial_size.bind(this, this), timeout);
                }.bind(this));

            } else {
                if (this.resize_timer) {
                    clearTimeout(this.resize_timer);
                }
            }
        }
    };

    return self;

}());
