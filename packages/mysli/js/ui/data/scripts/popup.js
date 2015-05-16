mjud.add('popup', function() {

    'use strict';

    var ui = mysli.js.ui;
    var panel = new ui.Panel({uid: 'mjud-popup'});
    var titlebar = new ui.Titlebar({style: 'default'});
    var popup = new ui.Popup();

    popup.push(new ui.HTML('Hello world!'));

    // Titlebar
    titlebar.push(new ui.Button({
        icon: 'close'
    })).connect('click', function () {
        panel.close();
    });

    titlebar.push(new ui.Label({text: "Popup", type: ui.Label.TITLE}), {expanded: true});

    var container = new ui.Container();

    // Default
    var container = new ui.Box({orientation: ui.Box.VERTICAL});
    container.push([
        new ui.Button({label: 'Display popup'})
    ]);

    container.get(0).connect('click', function (e) {
        popup.show(e);
    });

    panel.front.push(titlebar);
    panel.front.push(container, {padding: true});

    return panel;
});
