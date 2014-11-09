mysli.cms.dash.module = function (request) {

    'use strict';

    var registry = {},
        self = {};

    self = {
        add : function (id, module, fetch_dependencies) {
            if (typeof registry[id] !== 'undefined') {
                throw new Error('Module already added: `'+id+'`.');
            }
            if (fetch_dependencies) {
                // Fetch dependencies here...
            } else {
                params = [];
            }
            if (typeof module === 'function') {
                registry[id] = module.apply(module, params);
            } else {
                registry[id] = module;
            }
        }
    };

    return self;
};
