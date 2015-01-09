(function () {

    'use strict';

    function mk_navigation() {
        var ui = mysli.web.ui,
            panel = new ui.panel('mysli-cms-dash-navigation', {
                flippable: true
            });

        return panel;
    }
    function mk_introduction() {
        var ui = mysli.web.ui,
            panel = new ui.panel('mysli-cms-dash-introduction', {
                size : 'big',
                min_size : 'default'
            }),
            titlebar = new ui.titlebar({color: 'default'});

        titlebar.push(new ui.button({
            icon: 'times',
            style_flat: true
        })).connect('click', function () {
            panel.destroy();
        });

        panel.front.push(titlebar);
        return panel;
    }
    function mk_aisde() {
        var ui = mysli.web.ui,
            panel = new ui.panel('mysli-cms-dash-aside');

        return panel;
    }

    var panels = new mysli.web.ui.panel_container();

    panels.push(mk_navigation());
    panels.push(mk_introduction());
    panels.push(mk_aisde());
    panels.show();

    window.panels = panels;
}());
