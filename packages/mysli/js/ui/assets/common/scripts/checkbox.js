mjud.add('checkbox', function() {

    'use strict';

    var ui = mysli.js.ui;
    var panel = new ui.Panel({uid: 'mjud-checkbox'});
    var titlebar = new ui.Titlebar({style: 'default'});

    // Titlebar
    titlebar.push(new ui.Button({
        icon: 'close'
    })).connect('click', function () {
        panel.close();
    });
    titlebar.push(new ui.Label({text: "Checkbox", type: ui.Label.TITLE}), {expanded: true});

    // Container
    var container = new ui.Container();
    container.push(new ui.Label({text: "Default Style"}), {padding: [true, false, false, false]});
    container.push(new ui.Checkbox({label: "Default..."}), {padding: true});
    container.push(new ui.Checkbox({label: "I'm disabled...", disabled: true}), {padding: true});
    container.push(new ui.Checkbox({label: "I'm disabled checked...", checked: true, disabled: true}), {padding: true});
    container.push(new ui.Checkbox({label: "I'm flat...", flat: true}), {padding: true});

    container.push(new ui.Label({text: "Alt Style"}), {padding: [true, false, false, false]});
    container.push(new ui.Checkbox({style: 'alt', label: "Default..."}), {padding: true});
    container.push(new ui.Checkbox({style: 'alt', label: "I'm disabled...", disabled: true}), {padding: true});
    container.push(new ui.Checkbox({style: 'alt', label: "I'm disabled checked...", checked: true, disabled: true}), {padding: true});
    container.push(new ui.Checkbox({style: 'alt', label: "I'm flat...", flat: true}), {padding: true});

    // Source
    panel.front.push(titlebar);
    panel.front.push(container, {padding: true});

    return panel;
});
