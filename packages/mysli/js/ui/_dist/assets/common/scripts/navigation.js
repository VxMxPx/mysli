mjud.add('navigation', function () {

    'use strict';

    var ui = mysli.js.ui;
    var panel = new ui.Panel({
        uid: 'mjud-navigation',
        width: ui.Panel.SIZE_SMALL
    });
    var usermeta = new ui.Titlebar({flat: true});
    var navigation = new ui.Navigation({
        introduction: "Introduction",
        button: "Button",
        tab: "Tab",
        panel: "Panel"
    }, {style: 'alt'});

    panel.front.style = 'alt';

    navigation.connect('action', function (id, e, self) {
        e.stopPropagation();
        mjud.open(id);
    });

    usermeta.push(new ui.Label({text: 'Mysli JS Ui :: Developer', type: ui.Label.TITLE}));
    panel.front.push(usermeta);
    panel.front.push(navigation);

    return panel;
});
