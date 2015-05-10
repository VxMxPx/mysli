mjud.add('radio', function() {

    'use strict';

    var ui = mysli.js.ui;
    var panel = new ui.Panel({uid: 'mjud-radio'});
    var titlebar = new ui.Titlebar({style: 'default'});

    // Titlebar
    titlebar.push(new ui.Button({
        icon: 'close'
    })).connect('click', function () {
        panel.close();
    });
    titlebar.push(new ui.Label({text: "Radio", type: ui.Label.TITLE}), {expanded: true});

    // Container
    var container = new ui.Container();
    container.push(new ui.Label({text: "Default Style"}), {padding: [true, false, false, false]});
    container.push(new ui.Radio({label: "Default...", toggle: true}), {padding: true});
    container.push(new ui.Radio({label: "I'm disabled...", disabled: true}), {padding: true});
    container.push(new ui.Radio({label: "I'm disabled checked...", checked: true, disabled: true}), {padding: true});
    container.push(new ui.Radio({label: "I'm flat...", toggle: true, flat: true}), {padding: true});

    container.push(new ui.Label({text: "Alt Style"}), {padding: [true, false, false, false]});
    container.push(new ui.Radio({style: 'alt', label: "Default...", toggle: true}), {padding: true});
    container.push(new ui.Radio({style: 'alt', label: "I'm disabled...", disabled: true}), {padding: true});
    container.push(new ui.Radio({style: 'alt', label: "I'm disabled checked...", checked: true, disabled: true}), {padding: true});
    container.push(new ui.Radio({style: 'alt', label: "I'm flat...", flat: true, toggle: true}), {padding: true});

    container.push(new ui.Label({text: "Group"}), {padding: [true, false, false, false]});
    container.push(new ui.RadioGroup([
        {label: "Option 1"},
        {label: "Option 2"},
        {label: "Option 3"},
        {label: "Option 4"}
    ]));


    container.push(new ui.Label({text: "Group Vertical"}), {padding: [true, false, false, false]});
    container.push(new ui.RadioGroup([
        {label: "A"},
        {label: "B"},
        {label: "C"},
        {label: "D"},
        {label: "E"}
    ], {orientation: ui.Box.VERTICAL}));

    // Source
    panel.front.push(titlebar);
    panel.front.push(container, {padding: true, scroll: ui.Cell.SCROLL_Y});

    return panel;
});
