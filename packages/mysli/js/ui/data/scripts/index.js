(function () {

    'use strict';

    window.mjud = (function() {

        var panels = new mysli.js.ui.PanelContainer();
        var collection = {};

        function add(module, call) {
            if (typeof collection[module] !== 'undefined') {
                console.warn('Module is already registered.');
            }
            collection[module] = call;
        }

        function run(module) {
            var panel;
            panel = collection[module]();

            if (module !== 'navigation') {
                panel.connect('close', function () {
                    collection[module] = undefined;
                });
            }
            panels.insert(panel, 'mjud-navigation');
        }

        function open(id) {
            var panel = panels.get("mjud-"+id);
            if (!panel) {
                if (typeof collection[id] == 'function') {
                    run(id);
                } else {
                    //nav.get(id).set_busy(true);
                    $.getScript('?js='+id, function (_, __, jqxhr) {
                        // nav.get(id).set_busy(false);
                        if (jqxhr.status !== 200) {
                            // TODO: Show proper alert!
                            console.log('Request failed!');
                        } else {
                            if (typeof collection[id] == 'function') {
                                run(id);
                            }
                        }
                    });
                }
            } else {
                panel.focus = true;
            }
        }

        // Export Public Methods
        return { panels: panels, add: add, open: open };
    }());

    $('body').prepend(mjud.panels.element);
    mjud.open('navigation');
    // mjud.open('introduction');

}());
