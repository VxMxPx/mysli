(function () {

    'use strict';

    function mk_navigation() {
        var ui = mysli.web.ui,
            panel = new ui.panel('mysli-cms-dash-navigation'),
            usermeta = new ui.titlebar({style: 'flat'});

        panel.front.set_style('alt');

        usermeta.push(new ui.title('Mysli Web Ui :: Developer'));
        panel.front.push(usermeta);
        panel.front.push(new ui.navigation());

        return panel;
    }
    function mk_introduction() {
        var ui = mysli.web.ui,
            panel = new ui.panel('mysli-cms-dash-introduction', {
                size : 'big',
                min_size : 'default'
            }),
            titlebar = new ui.titlebar({color: 'default'}),
            content = new ui.html();

        titlebar.push(new ui.button({
            icon: 'times',
            style_flat: true
        })).connect('click', function () {
            panel.destroy();
        });
        titlebar.push(new ui.title("Introduction"), true);

        panel.front.push(titlebar);
        panel.front.push(content);

        content.set_busy(true);
        $.get('?html=introduction').done(function (data) {
            content.set_busy(false);
            content.push(data);
        });

        return panel;
    }

    var panels = new mysli.web.ui.panel_container();

    panels.push(mk_navigation());
    panels.push(mk_introduction());
    panels.show();
}());
