Mysli          = typeof Mysli          === 'object' ? Mysli          : {};
Mysli.Cms      = typeof Mysli.Cms      === 'object' ? Mysli.Cms      : {};
Mysli.Cms.Dash = typeof Mysli.Cms.Dash === 'object' ? Mysli.Cms.Dash : {};

Mysli.Cms.Dash.Init = function ($, Ui) {

    'use strict';

    // Self, appended basic config, moduels registry
    var Dash = {},
        registry = {
            'mysli/cms/dash' : Dash
        };

    // Export dash
    Dash = {
        add : function (name, dependencies, module) {
        }
    };

    // I shall be globally available
    window.Dash = Dash;
    // I shall run only once
    Mysli.Cms.Dash = null;
};
