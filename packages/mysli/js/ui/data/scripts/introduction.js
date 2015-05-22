mjud.add('introduction', function() {

    'use strict';

    var ui = mysli.js.ui,
        panel = new ui.Panel({
            uid: 'mjud-introduction',
            width: ui.Panel.SIZE_BIG
        }),
        titlebar = new ui.Titlebar(),
        content = new ui.HTML();

    titlebar.push(new ui.Button({
        icon: 'close'
    })).connect('click', function () {
        panel.close();
    });
    titlebar.push(new ui.Label({text: "Introduction", type: ui.Label.TITLE}), {expanded: true});

    panel.front.push(titlebar);
    panel.front.push(content);

    //content.set_busy(true);
    $.get('?html=introduction').done(function (data) {
        // content.set_busy(false);
        content.push(data);
    });

    return panel;

});
