mjud.add('entry', function() {

    'use strict';

    var ui = mysli.js.ui;
    var panel = new ui.Panel({uid: 'mjud-entry'});
    var titlebar = new ui.Titlebar({style: 'default'});

    // Titlebar
    titlebar.push(new ui.Button({
        icon: 'close'
    })).connect('click', function () {
        panel.close();
    });
    titlebar.push(new ui.Label({text: "Entry Examples", type: ui.Label.TITLE}), {expanded: true});

    // Container
    var container = new ui.Container();
    container.push(new ui.Label({text: "Default Style"}), {padding: true});
    container.push(new ui.Entry(), {padding: true});
    container.push(new ui.Entry({placeholder: "I have placeholder..."}), {padding: true});
    container.push(new ui.Entry({label: "I have label..."}), {padding: true});
    container.push(new ui.Entry({placeholder: "I'm a password...", type: ui.Entry.TYPE_PASSWORD}), {padding: true});
    container.push(new ui.Entry({placeholder: "I'm disabled...", disabled: true}), {padding: true});
    container.push(new ui.Entry({placeholder: "I'm flat...", flat: true}), {padding: true});

    container.push(new ui.Label({text: "Alt Style"}), {padding: true});
    container.push(new ui.Entry({style: 'alt'}), {padding: true});
    container.push(new ui.Entry({style: 'alt', placeholder: "I have placeholder..."}), {padding: true});
    container.push(new ui.Entry({style: 'alt', label: "I have label..."}), {padding: true});
    container.push(new ui.Entry({style: 'alt', placeholder: "I'm a password...", type: ui.Entry.TYPE_PASSWORD}), {padding: true});
    container.push(new ui.Entry({style: 'alt', placeholder: "I'm disabled...", disabled: true}), {padding: true});
    container.push(new ui.Entry({style: 'alt', placeholder: "I'm flat...", flat: true}), {padding: true});


    // Source
    panel.front.push(titlebar);
    panel.front.push(container, {padding: true});

    return panel;
});
