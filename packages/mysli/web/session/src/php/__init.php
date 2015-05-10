<?php

namespace mysli\web\session;

__use(__namespace__, '
    mysli.util.config
    mysli.framework.fs/fs,dir
');

class __init
{
    static function __init()
    {
        session::discover();
    }

    static function enable()
    {
        $c = config::select('mysli.web.session');
        $c->merge([
            'cookie_name'        => 'mysli_session',
            'require_ip'         => false,
            'require_agent'      => false,
            'expires'            => 60 * 60 * 24 * 7,
            'change_id_on_renew' => false,
        ]);

        return $c->save() &&
            dir::create(fs::datpath('mysli/web/session/sessions'));
    }

    static function disable()
    {
        return config::select('mysli.web.session')->destroy() &&
            dir::remove(fs::datpath('mysli/web/session/sessions'));
    }
}
