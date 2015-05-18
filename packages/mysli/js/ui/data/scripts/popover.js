mjud.add('popover', function() {

    'use strict';

    var ui = mysli.js.ui;
    var panel = new ui.Panel({uid: 'mjud-popover'});
    var titlebar = new ui.Titlebar({style: 'default'});
    var popover = new ui.Popover();

    popover.push(new ui.HTML('Hello world!'));

    // Titlebar
    titlebar.push(new ui.Button({
        icon: 'close'
    })).connect('click', function () {
        panel.close();
    });

    titlebar.push(new ui.Label({text: "Popover", type: ui.Label.TITLE}), {expanded: true});

    var container = new ui.Container();

    // Default
    var container = new ui.Box({orientation: ui.Box.VERTICAL});
    container.push([
        new ui.Button({label: 'Top'}),
        new ui.Button({label: 'Bottom'}),
        new ui.Button({label: 'Left'}),
        new ui.Button({label: 'Right'}),
    ]);

    container.get(0).connect('click', function (e, self) {
        popover.position = ui.Popover.POSITION_TOP;
        popover.show(self);
    });
    container.get(1).connect('click', function (e, self) {
        popover.position = ui.Popover.POSITION_BOTTOM;
        popover.show(self);
    });
    container.get(2).connect('click', function (e, self) {
        popover.position = ui.Popover.POSITION_LEFT;
        popover.show(self);
    });
    container.get(3).connect('click', function (e, self) {
        popover.position = ui.Popover.POSITION_RIGHT;
        popover.show(self);
    });

    panel.front.push(titlebar);
    panel.front.push(container, {padding: true});

    return panel;
});
