mysli.web.ui.mixins.root = (function () {

    // --- Private ---

    var ui = mysli.web.ui,
        template = '<div class="ui-root ui-widget"></div>';

    /// This is internal function to set element's size to window's size
    function set_size_to_window() {
        this.set_size({
            width:  $(window).outerWidth(),
            height: $(window).outerHeight()
        });
    }

    // --- Public ---

    /// Show (append) root element (and all containing elements)
    /// to the body
    function show () {
        this.parent.append(this.elements[0]);
    }

    /// Weather page should track resize event(s)
    /// @param {boolean} status
    /// @param {integer} timeout
    function set_track_resize (status, timeout) {
        if (status) {

            timeout = timeout || 500;

            $(window).on('resize', function () {
                if (this.resize_timer) {
                    clearTimeout(this.resize_timer);
                }
                this.resize_timer = setTimeout(
                    set_size_to_window.bind(this), timeout);
            }.bind(this));

        } else {
            if (this.resize_timer) {
                clearTimeout(this.resize_timer);
            }
        }
    }

    return function () {

        ui.mixins.container.call(this);

        this.elements.push($(template));
        this.parent = $('body');
        this.resize_timer = false;
        this.elements[0].css({top: 0, left: 0, position: 'relative'});
        this.parent.css('overflow', 'hidden');

        set_size_to_window.call(this);

        this.show = show;
        this.set_track_resize = set_track_resize;

        return this;
    };

}());
