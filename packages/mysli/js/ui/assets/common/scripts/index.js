(function () {

    'use strict';

    var panels = new mysli.js.ui.PanelContainer(),
        creator = {
            mk_navigation: function() {
                var ui = mysli.js.ui,
                    panel = new ui.Panel({
                        uid: 'mysli-cms-dash-navigation',
                        size: ui.Panel.SIZE_SMALL
                    }),
                    usermeta = new ui.Titlebar({flat: true}),
                    navigation = new ui.Navigation({
                        introduction: "Introduction",
                        buttons: "Buttons",
                        tabs: "Tabs"
                    }, {style: 'alt'});

                panel.front.style = 'alt';
                navigation.connect('action', function (id, e, self) {
                    e.stopPropagation();
                    open_panel(id, self);
                });

                usermeta.push(new ui.Label({text: 'Mysli JS Ui :: Developer'}));
                panel.front.push(usermeta);
                panel.front.push(navigation);

                return panel;
            },
            mk_introduction: function () {
                var ui = mysli.js.ui,
                    panel = new ui.Panel({
                        uid: 'mysli-cms-dash-introduction',
                        size: ui.Panel.SIZE_BIG,
                        min_size: ui.Panel.SIZE_NORMAL
                    }),
                    titlebar = new ui.Titlebar(),
                    content = new ui.HTML();

                titlebar.push(new ui.Button({
                    icon: 'close',
                    flat: true
                })).connect('click', function () {
                    panel.close();
                });
                titlebar.push(new ui.Label({text: "Introduction"}), {expanded: true});

                panel.front.push(titlebar);
                panel.front.push(content);

                //content.set_busy(true);
                $.get('?html=introduction').done(function (data) {
                    // content.set_busy(false);
                    content.push(data);
                });

                panel.connect('set-focus', function (status) {
                    console.log('Focus switched to: '+status);
                });

                return panel;
            }
        };

    function open_panel(id, nav) {
        var panel = panels.get("mysli-cms-dash-"+id);
        if (!panel) {
            if (typeof creator['mk_'+id] == 'function') {
                panels.insert(creator['mk_'+id](), 'mysli-cms-dash-navigation');
            } else {
                //nav.get(id).set_busy(true);
                $.getScript('?js='+id, function (_, __, jqxhr) {
                    // nav.get(id).set_busy(false);
                    if (jqxhr.status !== 200) {
                        // TODO: Show proper alert!
                        console.log('Request failed!');
                    } else {
                        if (typeof creator['mk_'+id] == 'function') {
                            panels.insert(creator['mk_'+id](), 'mysli-cms-dash-navigation');
                        }
                    }
                });
            }
        } else {
            panel.focus = true;
        }
    }

    $('body').prepend(panels.element);
    panels.push(creator.mk_navigation());
    panels.push(creator.mk_introduction());
    // panels.show();

    // Export module register
    window.mwu_dev_module = {
        add: function (module, call) {
            if (typeof creator[module] !== 'undefined') {
                throw new Error('Module is already registered.');
            }
            creator[module] = call;
        }
    };
}());
