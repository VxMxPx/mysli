;(function ($, Mysli) {

    'use strict';

    var element = $('body > #login'),
        submitButton = new Mysli.UI.Button(element.find('button[type=submit]'));

    element.on('submit', 'form', function (e) {
        e.preventDefault();
        submitButton.busy(true);
        $.post('login', $(this).serialize(), function (response, status, xmlhrq) {
            submitButton.busy(false);
            if (xmlhrq.status === 200) {
                if (response.status === 'success') {
                    Mysli.Dashboard.Login.hide();
                    Mysli.Dashboard.start(response.token);
                } else {
                    var alert = $('<div class="alert"><div class="message warn">' + response.message + '</div></div>');
                    alert.prependTo(element.find('.box'));
                    setTimeout(function() {
                        alert.fadeOut();
                    }, 3000);
                }
            }
        });
    });

    Mysli.Dashboard.Login = {
        show : function () {
            element.fadeIn();
        },
        hide : function () {
            element.fadeOut();
        }
    };

}(Zepto, Mysli));
