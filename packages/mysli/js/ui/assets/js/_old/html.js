mysli.js.ui.html = (function () {

    'use strict';

    var ui = mysli.js.ui,
        common = mysli.js.common,
        template = '<div class="ui-html ui-widget" />';

    var html = function () {

        // Define element and uid
        this.$element = $(template);
        this.uid = ui.util.uid(this.$element);

        this.collection = new common.arr();
    };

    html.prototype = {
        constructor: html,
    };

    ui.extend.collection(html);

    return html;
}());
