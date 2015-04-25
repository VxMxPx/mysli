mysli.js.ui.box = (function () {

    'use strict';

    var ui = mysli.js.ui,
        common  = mysli.js.common,
        template = '<div class="ui-box ui-widget" />';

    var box = function (options) {

        // Define element and uid
        this.$element = $(template);
        this.uid = ui.util.uid(this.$element);

        // Collection
        this.collection = new common.arr();

        // Properties
        this._ = common.merge({
            orientation: 'horizontal'
        }, options);

        // Apply orientation
        if (this.orientation === 'vertical') {
            var row = $('<div class="row" />');
            this.$element.append(row);
            this.$target = row;
        } else {
            this.$target = this.$element;
        }
    };

    box.prototype = {

        constructor: box,

        event: ui.extend.event([
            // Support for native dom events.
            '<native>'
        ])
    };

    ui.extend.collection(box, {
        target: '$target',
        wrapper: false,
        push: function () {
            if (this.orientation === 'horizontal') {
                return '<div class="ui-row"><div class="cell collection-targer" /></div>';
            } else {
                return true;
            }
        },
        cellapi: {
            expanded: function (status) {
                if (typeof status !== 'undefined') {
                    this[status ? 'addClass' : 'removeClass']('cell-expanded');
                } else {
                    this.hasClass('cell-expanded');
                }
            }
        }
    });

    return box;

}());
