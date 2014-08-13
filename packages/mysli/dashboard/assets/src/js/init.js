;(function ($, Mysli) {

    'use strict';

    function getToken() {
        return null;
    }

    // get token
    var token   = getToken(),
        element = $('#dashboard');

    if (!token) {
        Mysli.Dashboard.Login.show();
    }

    // init request object
    Mysli.Dashboard.start = function (token) {
        // Mysli.Dashboard.Request.setToken(token);
        $.get('navigation?token=' + token, function (response, status) {
            console.log(response);
        });
    };

}(Zepto, Mysli));
