(function () {

    'use strict';

    var mwu_dev_module = {
        add: function (module, call) {
            if (typeof creator[module] !== 'undefined') {
                console.warn('Module is already registered.');
            }
            creator[module] = call;
        },
        run: function (module, panels) {
            var panel;
            panel = creator[module]();
            if (module !== 'mk_introduction' && module !== 'mk_navigation') {
                panel.connect('close', function () {
                    creator[module] = undefined;
                });
            }
            panels.insert(panel, 'mysli-cms-dash-navigation');
        }
    };
    var panels = new mysli.js.ui.PanelContainer();
    var creator = {
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

                return panel;
            }
        };

    function open_panel(id, nav) {
        var panel = panels.get("mysli-cms-dash-"+id);
        if (!panel) {
            if (typeof creator['mk_'+id] == 'function') {
                mwu_dev_module.run('mk_'+id, panels);
            } else {
                //nav.get(id).set_busy(true);
                $.getScript('?js='+id, function (_, __, jqxhr) {
                    // nav.get(id).set_busy(false);
                    if (jqxhr.status !== 200) {
                        // TODO: Show proper alert!
                        console.log('Request failed!');
                    } else {
                        if (typeof creator['mk_'+id] == 'function') {
                            mwu_dev_module.run('mk_'+id, panels);
                        }
                    }
                });
            }
        } else {
            panel.focus = true;
        }
    }

    $('body').prepend(panels.element);
    mwu_dev_module.run('mk_navigation', panels);
    mwu_dev_module.run('mk_introduction', panels);
    //panels.push(creator.mk_navigation());
    //panels.push(creator.mk_introduction());
     //panels.show();

    window.mwu_dev_module = mwu_dev_module;
}());
