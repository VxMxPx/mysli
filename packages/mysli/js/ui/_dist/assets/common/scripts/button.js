mjud.add('button', function() {

    'use strict';

    var ui = mysli.js.ui;
    var panel = new ui.Panel({uid: 'mjud-buttons', width: ui.Panel.SIZE_BIG});
    var titlebar = new ui.Titlebar({style: 'default'});

    // Titlebar
    titlebar.push(new ui.Button({
        icon: 'close'
    })).connect('click', function () {
        panel.close();
    });

    titlebar.push(new ui.Label({text: "Buttons Examples", type: ui.Label.TITLE}), {expanded: true});

    // Tabs
    //notebook.connect('action', function (id, self) {
    //    // Pass
    //});

    var container = new ui.Container();

    // Default
    var b_default = new ui.Box({orientation: ui.Box.VERTICAL});
    b_default.push(new ui.Button({label: 'Default'}));
    b_default.push(new ui.Button({label: 'Flat', flat: true}));
    b_default.push(new ui.Button({label: 'Disabled', disabled: true}));
    b_default.push(new ui.Button({label: 'Flat Disabled', flat: true, disabled: true}));

    // Alt
    var b_alt = new ui.Box({orientation: ui.Box.VERTICAL});
    b_alt.push(new ui.Button({label: 'Default', style: 'alt'}));
    b_alt.push(new ui.Button({label: 'Flat', flat: true, style: 'alt'}));
    b_alt.push(new ui.Button({label: 'Disabled', disabled: true, style: 'alt'}));
    b_alt.push(new ui.Button({label: 'Flat Disabled', flat: true, disabled: true, style: 'alt'}));

    // Primary
    var b_primary = new ui.Box({orientation: ui.Box.VERTICAL});
    b_primary.push(new ui.Button({label: 'Default', style: 'primary'}));
    b_primary.push(new ui.Button({label: 'Flat', flat: true, style: 'primary'}));
    b_primary.push(new ui.Button({label: 'Disabled', disabled: true, style: 'primary'}));
    b_primary.push(new ui.Button({label: 'Flat Disabled', flat: true, disabled: true, style: 'primary'}));

    // Attention
    var b_attention = new ui.Box({orientation: ui.Box.VERTICAL});
    b_attention.push(new ui.Button({label: 'Default', style: 'attention'}));
    b_attention.push(new ui.Button({label: 'Flat', flat: true, style: 'attention'}));
    b_attention.push(new ui.Button({label: 'Disabled', disabled: true, style: 'attention'}));
    b_attention.push(new ui.Button({label: 'Flat Disabled', flat: true, disabled: true, style: 'attention'}));

    // Confirm
    var b_confirm = new ui.Box({orientation: ui.Box.VERTICAL});
    b_confirm.push(new ui.Button({label: 'Default', style: 'confirm'}));
    b_confirm.push(new ui.Button({label: 'Flat', flat: true, style: 'confirm'}));
    b_confirm.push(new ui.Button({label: 'Disabled', disabled: true, style: 'confirm'}));
    b_confirm.push(new ui.Button({label: 'Flat Disabled', flat: true, disabled: true, style: 'confirm'}));

    container.push(new ui.Label({text: "Default:"}), {padding: [false, false, 5, false]});
    container.push(b_default);
    container.push(new ui.Label({text: "Alt:"}), {padding: [true, false, 5, false]});
    container.push(b_alt);
    container.push(new ui.Label({text: "Primary:"}), {padding: [true, false, 5, false]});
    container.push(b_primary);
    container.push(new ui.Label({text: "Attention:"}), {padding: [true, false, 5, false]});
    container.push(b_attention);
    container.push(new ui.Label({text: "Confirm:"}), {padding: [true, false, 5, false]});
    container.push(b_confirm);

    // Source

    panel.front.push(titlebar);
    panel.front.push(container, {padding: true});


    return panel;
});
