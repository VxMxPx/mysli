mjud.add('popover', function() {

    'use strict';

    var ui = mysli.js.ui;
    var panel = new ui.Panel({uid: 'mjud-popover'});
    var titlebar = new ui.Titlebar({style: 'default'});
    var right_menu = new ui.Popover({ center: false, pointer: false, position: ui.Popover.POSITION_BOTTOM });

    right_menu.push(new ui.Label({text: 'You have activated right click!'}), {padding: true});
    right_menu.push(new ui.Button({
        label: 'Close!'
    }), {padding: true}).connect('click', function (e) {
        right_menu.hide();
        panel.close();
    });

    function popover(widget, position)
    {
        if (typeof widget.popover === 'undefined')
        {
            var buttons = new ui.Box({ orientation: ui.Box.VERTICAL });
            buttons.push(new ui.Button({ label: 'Close', flat: true }));
            buttons.push(new ui.Button({ label: 'Confirm!', style: 'confirm' }), {align: ui.Cell.ALIGN_RIGHT});
            widget.popover = new ui.Popover({ width: 200 });
            // widget.popover = new ui.Popover({
            //     cell: {
            //         padding: [10, 10, 10, 10],
            //         smart_padding: true
            //     }
            // });
            widget.popover.push(new ui.Label({ text: 'Hi there!' }), {padding: true});
            widget.popover.push(new ui.Entry({ placeholder: 'Enter your name...' }), {padding: [null, true, true, true]});
            widget.popover.push(new ui.Divider(), {padding: {bottom: true}});
            widget.popover.push(buttons, {padding: [null, true, true, true]});
            widget.popover.position = position;
        }
        widget.popover.show(widget);
    }

    // Titlebar
    titlebar.push(new ui.Button({
        icon: 'close'
    })).connect('click', function () {
        panel.close();
    });

    titlebar.push(new ui.Label({text: "Popover", type: ui.Label.TITLE}), {expanded: true});

    // Default
    var container = new ui.Box({orientation: ui.Box.VERTICAL});
    container.push([
        new ui.Button({label: 'Top'}),
        new ui.Button({label: 'Bottom'}),
        new ui.Button({label: 'Left'}),
        new ui.Button({label: 'Right'}),
    ]);

    container.get(2, true).align = ui.Cell.ALIGN_RIGHT;
    container.get(3, true).align = ui.Cell.ALIGN_RIGHT;

    container.get(0).connect('click', function (e, self) {
        popover(self, ui.Popover.POSITION_TOP);
    });
    container.get(1).connect('click', function (e, self) {
        popover(self, ui.Popover.POSITION_BOTTOM);
    });
    container.get(2).connect('click', function (e, self) {
        popover(self, ui.Popover.POSITION_LEFT);
    });
    container.get(3).connect('click', function (e, self) {
        popover(self, ui.Popover.POSITION_RIGHT);
    });

    panel.front.push(titlebar);
    panel.front
        .push(new ui.Button({label: "Hello"}), {padding: true, fill: true})
        .connect('click', function (e, self) {
            popover(self, [ui.Popover.POSITION_TOP, ui.Popover.POSITION_BOTTOM]);
        });
    panel.front.push(new ui.Label({ text: "Try to right click anywhere on this panel." }), {padding: true});
    panel.front.push(container, {padding: [80, true, true, true]});

    // panel.connect('on-context-menu', function () { return false; });
    panel.element.on('contextmenu', function (e) {
        right_menu.show(e);
        return false;
    });

    return panel;
});
