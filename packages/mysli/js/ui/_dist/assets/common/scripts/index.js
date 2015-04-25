(function () {

    'use strict';

    var ui = mysli.js.ui;
    var box = new ui.Box();

    box.push(new ui.Button({label: "Hi there!"}));

    $('body').append(box.element());

    window.box = box;

    // var panels = new mysli.js.ui.panel_container(),
    //     creator = {
    //         mk_navigation: function() {
    //             var ui = mysli.js.ui,
    //                 panel = new ui.panel({
    //                     uid: 'mysli-cms-dash-navigation',
    //                     size: 'small'
    //                 }),
    //                 usermeta = new ui.titlebar({flat: true}),
    //                 navigation = new ui.navigation({
    //                     introduction: "Introduction",
    //                     buttons: "Buttons",
    //                     tabs: "Tabs"
    //                 });

    //             panel.front.style('alt');
    //             navigation.style('alt');
    //             navigation.event.connect('action', function (id, self) {
    //                 open_panel(id, self);
    //             });

    //             usermeta.push(new ui.title('Mysli JS Ui :: Developer'));
    //             panel.front.push(usermeta);
    //             panel.front.push(navigation);

    //             return panel;
    //         },
    //         mk_introduction: function () {
    //             var ui = mysli.js.ui,
    //                 panel = new ui.panel('mysli-cms-dash-introduction', {
    //                     size : 'big',
    //                     min_size : 'default'
    //                 }),
    //                 titlebar = new ui.titlebar({color: 'default'}),
    //                 content = new ui.html();

    //             titlebar.push(new ui.button({
    //                 icon: 'close',
    //                 style_flat: true
    //             })).connect('click', function () {
    //                 panel.destroy();
    //             });
    //             titlebar.push(new ui.title("Introduction"), true);

    //             panel.front.push(titlebar);
    //             panel.front.push(content);

    //             content.set_busy(true);
    //             $.get('?html=introduction').done(function (data) {
    //                 content.set_busy(false);
    //                 content.push(data);
    //             });

    //             return panel;
    //         }
    //     };

    // function open_panel(id, nav) {
    //     var panel = panels.get("mysli-cms-dash-"+id);
    //     if (!panel) {
    //         if (typeof creator['mk_'+id] == 'function') {
    //             panels.push_after('mysli-cms-dash-navigation', creator['mk_'+id]());
    //         } else {
    //             nav.get(id).set_busy(true);
    //             $.getScript('?js='+id, function (_, __, jqxhr) {
    //                 nav.get(id).set_busy(false);
    //                 if (jqxhr.status !== 200) {
    //                     // TODO: Show proper alert!
    //                     console.log('Request failed!');
    //                 } else {
    //                     if (typeof creator['mk_'+id] == 'function') {
    //                         panels.push_after('mysli-cms-dash-navigation', creator['mk_'+id]());
    //                     }
    //                 }
    //             });
    //         }
    //     } else {
    //         panel.set_focus(true);
    //     }
    // }

    // panels.push(creator.mk_navigation());
    // $('body').prepend(panels.$element);
    // // panels.push(creator.mk_introduction());
    // // panels.show();

    // // Export module register
    // window.mwu_dev_module = {
    //     add: function (module, call) {
    //         if (typeof creator[module] !== 'undefined') {
    //             throw new Error('Module is already registered.');
    //         }
    //         creator[module] = call;
    //     }
    // };
}());