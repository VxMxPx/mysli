mjud.add('popover', function() {

    'use strict';

    var ui = mysli.js.ui;
    var panel = new ui.Panel({uid: 'mjud-popover'});
    var titlebar = new ui.Titlebar({style: 'default'});

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
            widget.popover.push(new ui.Label({ text: 'Hi there!' }), {padding: [10, 10, 10, 10]});
            widget.popover.push(new ui.Entry({ placeholder: 'Enter your name...' }), {padding: [null, 10, 10, 10]});
            widget.popover.push(buttons, {padding: [null, 10, 10, 10]});
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

    var container = new ui.Container();

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
    panel.front.push(container, {padding: true});
    panel.front
        .push(new ui.Button({label: "Hello"}), {padding: true, fill: true})
        .connect('click', function (e, self) {
            popover(self, [ui.Popover.POSITION_TOP, ui.Popover.POSITION_BOTTOM]);
        })

    return panel;
});
