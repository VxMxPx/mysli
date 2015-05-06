mjud.add('tabbar', function() {

    'use strict';
    
    var ui = mysli.js.ui;
    var panel = new ui.Panel({uid: 'mjud-tabbar', width: ui.Panel.SIZE_BIG});
    var titlebar = new ui.Titlebar({style: 'default'});

    // Titlebar
    titlebar.push(new ui.Button({
        icon: 'close'
    })).connect('click', function () {
        panel.close();
    });
    titlebar.push(new ui.Label({text: "Tabbar Examples", type: ui.Label.TITLE}), {expanded: true});

//    var container = new ui.Container();

    // Default
//    container.push(new ui.Label({text: "Default:"}), {padding: [false, false, 5, false]});    
//    var b_default = new ui.Box({orientation: ui.Box.VERTICAL});
//    b_default.push([
//        new ui.Button({label: 'Default'}),
//        new ui.Button({label: 'Flat', flat: true}),
//        new ui.Button({label: 'Disabled', disabled: true}),
//        new ui.Button({label: 'Flat Disabled', flat: true, disabled: true})
//    ]);
//    container.push(b_default);

    // Source
    panel.front.push(titlebar);
    panel.front.push(new ui.Tabbar({
        examples: 'Examples',
        source: 'Source',
        doc: 'Documentation'
    }, {active: 'examples'}));
//    panel.front.push(container, {padding: true});

    return panel;
});
