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

    titlebar.push(new ui.Label({text: "Button", type: ui.Label.TITLE}), {expanded: true});

    var container = new ui.Container();

    // Default
    container.push(new ui.Label({text: "Default:"}), {padding: [false, false, 5, false]});
    var b_default = new ui.Box({orientation: ui.Box.VERTICAL});
    b_default.push([
        new ui.Button({label: 'Default'}),
        new ui.Button({label: 'Flat', flat: true}),
        new ui.Button({label: 'Disabled', disabled: true}),
        new ui.Button({label: 'Flat Disabled', flat: true, disabled: true})
    ]);
    container.push(b_default);

    // Alt
    container.push(new ui.Label({text: "Alt:"}), {padding: [true, false, 5, false]});
    var b_alt = new ui.Box({orientation: ui.Box.VERTICAL});
    b_alt.push([
        new ui.Button({label: 'Default', style: 'alt'}),
        new ui.Button({label: 'Flat', flat: true, style: 'alt'}),
        new ui.Button({label: 'Disabled', disabled: true, style: 'alt'}),
        new ui.Button({label: 'Flat Disabled', flat: true, disabled: true, style: 'alt'})
    ]);
    container.push(b_alt);

    // Primary
    container.push(new ui.Label({text: "Primary:"}), {padding: [true, false, 5, false]});
    var b_primary = new ui.Box({orientation: ui.Box.VERTICAL});
    b_primary.push([
        new ui.Button({label: 'Default', style: 'primary'}),
        new ui.Button({label: 'Flat', flat: true, style: 'primary'}),
        new ui.Button({label: 'Disabled', disabled: true, style: 'primary'}),
        new ui.Button({label: 'Flat Disabled', flat: true, disabled: true, style: 'primary'})
    ]);
    container.push(b_primary);

    // Attention
    container.push(new ui.Label({text: "Attention:"}), {padding: [true, false, 5, false]});
    var b_attention = new ui.Box({orientation: ui.Box.VERTICAL});
    b_attention.push([
        new ui.Button({label: 'Default', style: 'attention'}),
        new ui.Button({label: 'Flat', flat: true, style: 'attention'}),
        new ui.Button({label: 'Disabled', disabled: true, style: 'attention'}),
        new ui.Button({label: 'Flat Disabled', flat: true, disabled: true, style: 'attention'})
    ]);
    container.push(b_attention);

    // Confirm
    container.push(new ui.Label({text: "Confirm:"}), {padding: [true, false, 5, false]});
    var b_confirm = new ui.Box({orientation: ui.Box.VERTICAL});
    b_confirm.push([
        new ui.Button({label: 'Default', style: 'confirm'}),
        new ui.Button({label: 'Flat', flat: true, style: 'confirm'}),
        new ui.Button({label: 'Disabled', disabled: true, style: 'confirm'}),
        new ui.Button({label: 'Flat Disabled', flat: true, disabled: true, style: 'confirm'})
    ]);
    container.push(b_confirm);

    // Toggle
    container.push(new ui.Label({text: "Toggle:"}), {padding: [true, false, 5, false]});
    var b_toggle = new ui.Box({orientation: ui.Box.VERTICAL});
    b_toggle.push([
        new ui.Button({label: 'Default', toggle: true}),
        new ui.Button({label: 'Alt', toggle: true, style: 'alt'}),
        new ui.Button({label: 'Primary', toggle: true, style: 'primary'}),
        new ui.Button({label: 'Attention', toggle: true, style: 'attention'}),
        new ui.Button({label: 'Confirm', toggle: true, style: 'confirm'})
    ]);
    container.push(b_toggle);

    // Source
    panel.front.push(titlebar);
    panel.front.push(container, {padding: true});


    return panel;
});
