mjud.add('input', function() {

    'use strict';

    var ui = mysli.js.ui;
    var panel = new ui.Panel({uid: 'mjud-input'});
    var titlebar = new ui.Titlebar({style: 'default'});

    // Titlebar
    titlebar.push(new ui.Button({
        icon: 'close'
    })).connect('click', function () {
        panel.close();
    });
    titlebar.push(new ui.Label({text: "Input Examples", type: ui.Label.TITLE}), {expanded: true});

    // Container
    var container = new ui.Container();
    container.push(new ui.Label({text: "Default Style"}), {padding: true});
    container.push(new ui.Input(), {padding: true});
    container.push(new ui.Input({placeholder: "I have placeholder..."}), {padding: true});
    container.push(new ui.Input({label: "I have label..."}), {padding: true});
    container.push(new ui.Input({placeholder: "I'm a password...", type: ui.Input.TYPE_PASSWORD}), {padding: true});
    container.push(new ui.Input({placeholder: "I'm disabled...", disabled: true}), {padding: true});
    container.push(new ui.Input({placeholder: "I'm flat...", flat: true}), {padding: true});

    container.push(new ui.Label({text: "Alt Style"}), {padding: true});
    container.push(new ui.Input({style: 'alt'}), {padding: true});
    container.push(new ui.Input({style: 'alt', placeholder: "I have placeholder..."}), {padding: true});
    container.push(new ui.Input({style: 'alt', label: "I have label..."}), {padding: true});
    container.push(new ui.Input({style: 'alt', placeholder: "I'm a password...", type: ui.Input.TYPE_PASSWORD}), {padding: true});
    container.push(new ui.Input({style: 'alt', placeholder: "I'm disabled...", disabled: true}), {padding: true});
    container.push(new ui.Input({style: 'alt', placeholder: "I'm flat...", flat: true}), {padding: true});


    // Source
    panel.front.push(titlebar);
    panel.front.push(container, {padding: true});

    return panel;
});
