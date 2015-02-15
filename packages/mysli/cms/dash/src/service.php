<?php

namespace mysli\cms\dash;

__use(__namespace__, '
    mysli.cms.dash
    mysli.framework.json
    mysli.util.tplp
    mysli.util.i18n
    mysli.util.output
    mysli.web.request
    mysli.web.response
    mysli.web.token
    mysli.web.session
');

class service {

    static function run() {

        $token_id = request::get('mysli_token');

        // Valid token if exists...
        if ($token_id) {
            if (token::get($token_id)) {
                self::do_response(self::do_package_call(null));
            } else {
                response::set_status(response::status_401_unauthorized);
                self::do_response(dash::response_message(
                    "Seems your token expired.", dash::msg_warn));
            }
        } elseif (request::segment(1) === 'token') {
            if (($token = self::get_token())) {
                self::do_response(['token' => $token]);
            } else {
                response::set_status(404);
                self::do_response(dash::response_message(
                    "Not found!", dash::msg_warn));
            }
        } else {
            self::do_init();
        }
    }

    /**
     * Do a proper response.
     */
    private static function do_response($data) {
        if (is_object($data)) {
            if (method_exists($data, 'as_array')) {
                $data = $data->as_array();
            } else {
                response::set_status(500);
                return;
            }
        }

        output::clear();
        output::add(json::encode($data));
        echo response::get_status();
        if (response::get_status() === 0) {
            response::set_status(200);
        }
        return true;
    }
    /**
     * Get token from session if possible
     * @return mixed string|false
     */
    private static function get_token() {

        $user = session::user();
        if (!$user) {
            return false;
        }

        $token_id = $user->get_config('mysli/cms/dash/token');
        if (!$token_id) {
            return false;
        }
        return token::get($token_id);
    }
    /**
     * Handle init request.
     */
    private static function do_init() {
        $template = tplp::select('mysli/cms/dash');
        $template->set_translator(i18n::select('mysli/cms/dash'));
        output::add($template->render('init', ['title' => 'Index']));
        response::set_status(200);
    }
}
