<?php

namespace mysli\cms\dash;

__use(__namespace__, '
    mysli/util/tplp
    mysli/util/i18n
    mysli/util/output
    mysli/web/request
    mysli/web/session
');

class dash {
    static function run() {
        $token = request::get('mysli_token');
        // Valid token if exists...
        if ($token) {
            // Pass, for now...
            die('Token valid, here shall be your response...');
        } elseif (request::segment(1) === 'login') {
            return auth::get_login();
        } elseif (request::segment(1) === 'api') {
            if (request::get('mysli/cms/dash') === 'token') {
                // Get token from session
            } else {
                // Return that this is invalid request
            }
        } else {
            $template = tplp::select('mysli/cms/dash');
            $template->set_translator(i18n::select('mysli/cms/dash'));
            output::add($template->render('index', ['title' => 'Index']));
        }
    }
}
