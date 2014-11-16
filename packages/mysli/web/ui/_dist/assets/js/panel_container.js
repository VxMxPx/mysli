mysli.web.ui.panel_container = (function () {

    'use strict';

    var ui = mysli.web.ui;

    var self = function () {
        ui.mixins.root.call(self.prototype);
    };

    self.prototype = {
        constructor : self
    };

    return self;
}());
