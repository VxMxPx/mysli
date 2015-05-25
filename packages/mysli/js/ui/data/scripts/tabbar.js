// Register this module
mjud.add('tabbar', function() {

    'use strict';

    var sid = 'tabbar';

    var ui = mysli.js.ui;
    var panel = new ui.Panel({
        uid: 'mjud-tabbar',
        width: ui.Panel.SIZE_BIG
    });

    // Titlebar
    var titlebar = new ui.Titlebar();
    titlebar
        .push(new ui.Button({icon: 'close'}))
        .connect('click', function () {
            panel.close();
        });
    titlebar
        .push(
            new ui.Label({
                text: "Tabbar Examples",
                type: ui.Label.TITLE
            }),
            {expanded: true});
    panel.front.push(titlebar);


    // TABBAR
    var tabbar = new ui.Tabbar({
        add: {
            exa: 'Examples',
            src: 'Source',
            doc: 'Documentation'
        },
        // Set active tab to be examples
        active: 'exa',
        stack: new ui.Stack()
    });
    panel.front.push(tabbar);
    panel.front.push(tabbar.stack, { scroll: ui.Cell.SCROLL_Y });

    tabbar.stack.get('exa').push(new ui.Label("The tabbar above is an example for now...."), {padding: true});
    tabbar.stack.get('src').push(new ui.HTML(), {padding: true});
    tabbar.stack.get('doc').push(new ui.HTML(), {padding: true});

    tabbar.connect('action', function (id) {
        if (id === 'src' || id === 'doc')
        {
            tabbar.stack.get(id+' > 0').replace('Loading...');

            mjud.request('tabbar', id, function (data) {
                if (id === 'src')
                {
                    data = '<pre>' + data + '</pre>';
                }
                tabbar.stack.get(id+' > 0').replace(data);
            });
        }
    });

    return panel;
});
