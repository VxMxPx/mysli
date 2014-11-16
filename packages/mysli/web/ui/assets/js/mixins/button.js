mysli.web.ui.mixins.button = (function () {

    var ui = mysli.web.ui,
        template = '<button></button>';

    return function () {
        ui.mixins.widget.call(this);

        this.elements.push($(template));

        return this;
    };

}());
