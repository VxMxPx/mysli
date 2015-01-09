mysli.web.ui.mixins.box = (function () {

    // --- Private ---

    var ui = mysli.web.ui,
        template = '<div class="ui-box ui-widget"></div>';

    // --- Public ---

    /// Sets orientation for box. This can be called only once.
    /// @param {string} orientation
    function set_orientation(orientation) {

        if (this.orientation) {
            throw new Error('Orientation is already set.');
        }

        if (orientation === this.HORIZONTAL) {
            this.orientation = this.HORIZONTAL;
        } else if (orientation === this.VERTICAL) {
            this.orientation = this.VERTICAL;
        } else {
            throw new Error('Invalid `orientation` value.');
        }
    }

    return function (options) {

        ui.mixins.container.call(this);

        this.HORIZONTAL = 'orientation-horizontal';
        this.VERTICAL   = 'orientation-vertical';

        this.elements.push($(template));

        this.set_orientation = set_orientation;

        this.set_orientation(options.orientation || this.HORIZONTAL);
        if (this.orientation === this.VERTICAL) {
            var row = $('<div class="row" />');
            this.container.cid = 1;
            this.elements.push(row);
            this.elements[0].append(row);
        }

        return this;
    };

}());
