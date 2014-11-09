mysli.cms.dash.init = function (config, $, ui) {

    'use strict';

    var token   = false,
        dash    = mysli.cms.dash,
        module  = false;
    //     request = dash.request($, null, config.url),
    //     module  = dash.module(request);

    // module.add('mysli/web/ui', ui);
    // module.add('mysli/external/zepto', $);
    // module.add('mysli/cms/dash/request', request);
    // module.add('mysli/cms/dash/module', module);
    // module.add('mysli/cms/dash/init', true);

    //$('body').css('background-color', ui.colors.background_alt);

    $.getJSON(config.url+'token', function (data, status) {
        if (status !== 200) {
            mysli.cms.dash.login($, ui).show();
        } else {
            token = data.token;
        }
    });

    // Make module register globally available
    if (module) {
        window.__dmod = module.add;
    }

    // I shall run only once
    mysli.cms.dash.init = true;
};
