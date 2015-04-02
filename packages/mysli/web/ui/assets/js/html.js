mysli.web.ui.html = (function () {

    'use strict';

    var ui = mysli.web.ui,
        template = '<div class="ui-html ui-widget" />';

    var html = function () {
        this.$element = $(template);
        this.uid = ui._.uid(this.$element);
    };

    html.prototype = {
        add: function (content) {
            this.$element.append(content);
        }
    };

    return html;
}());
