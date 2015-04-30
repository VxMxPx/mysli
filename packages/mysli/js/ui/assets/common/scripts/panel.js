mjud.add('panel', function () {

    'use strict';

    var ui = mysli.js.ui;
    var panel = new ui.Panel({
        uid: 'mjud-panel',
        flippable: true
    });

    // Front side of the panel
    var titlebar_front = new ui.Titlebar();
    titlebar_front.push(new ui.Button({
        icon: 'close'
    })).connect('click', function () {
        panel.close();
    });
    titlebar_front.push(new ui.Label({text: "Panel Example", type: ui.Label.TITLE}), {expanded: true});
    titlebar_front.push(new ui.Button({
        icon: 'cog'
    })).connect('click', function () {
        panel.side = 'back';
    });

    panel.front.push(titlebar_front);
    panel.front.push(new ui.HTML("Hi! I'm a panel. Use `cog` button to flip me arround!"));

    // Backside of the panel!
    var titlebar_back = new ui.Titlebar({flat: true});
    titlebar_back.push(new ui.Button({
        icon: 'arrow-left'
    })).connect('click', function () {
        panel.side = 'front';
    });
    titlebar_back.push(new ui.Label({text: "Panel Example Back", type: ui.Label.TITLE}), {expanded: true});

    panel.back.style = 'alt';
    panel.back.push(titlebar_back);
    panel.back.push(new ui.HTML("Hi! This is a backside of a panel!"));

    return panel;
})
