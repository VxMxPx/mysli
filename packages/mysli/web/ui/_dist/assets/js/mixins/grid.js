mysli.web.ui.mixins.box = (function () {

    // --- Private ---

    var ui = mysli.web.ui,
        template = '<div class="ui-box ui-widget"></div>';

    // --- Public ---

    /// Set box orientation
    /// @param {string} orientation
    function set_orientation(orientation) {

    }

    return function () {
        ui.mixins.container.call(this);

        this.HORIZONTAL = 'orientation-horizontal';
        this.VERTICAL   = 'orientation-vertical';

        this.elements.push($(template));

        this.orientation = this.VERTICAL;

        return this;
    };

}());
