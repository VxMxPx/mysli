(function () {

    'use strict';

    var panels = new mysli.web.ui.panel_container(),
        creator = {
            mk_navigation: function() {
                var ui = mysli.web.ui,
                    panel = new ui.panel('mysli-cms-dash-navigation', {
                        size : 'small'
                    }),
                    usermeta = new ui.titlebar({style: 'flat'}),
                    navigation = new ui.navigation({
                        introduction: "Introduction",
                        buttons: "Buttons"
                    });

                panel.front.set_style('alt');
                navigation.set_style('alt');
                navigation.connect('action', function (id, self) {
                    open_panel(id);
                });


                usermeta.push(new ui.title('Mysli Web Ui :: Developer'));
                panel.front.push(usermeta);
                panel.front.push(navigation);

                return panel;
            },
            mk_introduction: function () {
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
            },
            mk_buttons: function() {
                var ui = mysli.web.ui,
                    panel = new ui.panel('mysli-cms-dash-buttons'),
                    titlebar = new ui.titlebar({color: 'default'});

                titlebar.push(new ui.button({
                    icon: 'times',
                    style_flat: true
                })).connect('click', function () {
                    panel.destroy();
                });
                titlebar.push(new ui.title("Buttons Examples"), true);

                panel.front.push(titlebar);

                return panel;
            }
        };

    function open_panel(id) {
        var panel = panels.get("mysli-cms-dash-"+id);
        if (!panel) {
            if (typeof creator['mk_'+id] == 'function') {
                panels.push_after('mysli-cms-dash-navigation', creator['mk_'+id]());
            } else {
                alert('Wooops, something ain\'t right!');
            }
        } else {
            panel.set_focus(true);
        }
    }

    panels.push(creator.mk_navigation());
    panels.push(creator.mk_introduction());
    panels.show();
}());
