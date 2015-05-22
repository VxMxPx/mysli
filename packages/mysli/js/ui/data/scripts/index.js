(function () {

    'use strict';

    window.mjud = (function() {

        var panels = new mysli.js.ui.PanelContainer();
        var collection = {};

        /**
         * Add a new module to the collection
         * @param {string}   id
         * @param {function} call
         */
        function add(id, call) {
            if (typeof collection[id] !== 'undefined') {
                console.warn('Module is already registered.');
            }
            collection[id] = call;
        }

        /**
         * Run a particular module
         * @param  {string} id
         */
        function run(id) {
            var panel;
            panel = collection[id]();

            if (id !== 'navigation') {
                panel.connect('close', function () {
                    collection[id] = undefined;
                });
            }
            panels.insert(panel, 'mjud-navigation');
        }

        /**
         * Request documentation or source (as a string) for particular module.
         * @param {string} id
         * @param {string} what doc|src
         * @param {function} call
         */
        function request(id, what, call) {
            $.get('?'+what+'='+id, call);
        }

        /**
         * Open a panel for particula module
         * @param  {string} id
         */
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
        return {
            panels: panels,
            add: add,
            request: request,
            open: open
        };
    }());

    $('body').prepend(mjud.panels.element);
    mjud.open('navigation');

}());
