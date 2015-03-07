mwu_dev_module.add('mk_buttons', function() {

    'use strict';

    var ui = mysli.web.ui,
        panel = new ui.panel('mysli-cms-dash-buttons'),
        titlebar = new ui.titlebar({color: 'default'});

    titlebar.push(new ui.button({
        icon: 'close',
        style_flat: true
    })).connect('click', function () {
        panel.destroy();
    });
    titlebar.push(new ui.title("Buttons Examples"), true);

    panel.front.push(titlebar);

    return panel;
});
