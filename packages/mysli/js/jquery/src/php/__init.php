<?php

namespace mysli\js\jquery;

__use(__namespace__, '
    mysli.framework.fs/dir
    mysli.util.config
    mysli.web.assets
');

class __init
{
    static function enable()
    {
        $c = config::select('mysli.js.jquery');
        $c->merge([
            // Base URL from which jQuery will be loaded (if not local)
            'remote_url'  => 'http://code.jquery.com/jquery-{version}.js',
            // Default version .min will be append
            'version'     => '2.1.1',
            // Development version
            'dev_version' => '2.1.1',
            // Weather load script from local source.
            // Will use curl to acquire script from remote URL
            'local'       => false,
            // Weather dev. version should be used.
            'development' => false
        ]);

        return dir::create(assets::get_public_path('mysli.js.jquery'))
            && $c->save();
    }
    static function disable()
    {
        return dir::remove(assets::get_public_path('mysli.js.jquery'))
            && config::select('mysli.js.jquery')->destroy();
    }
}
