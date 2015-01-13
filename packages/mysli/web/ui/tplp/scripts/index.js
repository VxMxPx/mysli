(function () {

    'use strict';

    function mk_navigation() {
        var ui = mysli.web.ui,
            panel = new ui.panel('mysli-cms-dash-navigation'),
            usermeta = new ui.titlebar({style: 'flat'});
            // imageurl = null;

        // usermeta.push(new ui.image(imageurl));
        usermeta.push(new ui.title('Mysli Web Ui :: Developer'));

        panel.front.push(usermeta);
        panel.front.set_style('alt');
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

        $.get('?html=introduction').done(content.push.bind(content));

        return panel;
    }

    var panels = new mysli.web.ui.panel_container();

    panels.push(mk_navigation());
    panels.push(mk_introduction());
    panels.show();
}());
