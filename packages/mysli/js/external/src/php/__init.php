<?php

namespace mysli\js\external;

__use(__namespace__, '
    mysli.framework.ym
    mysli.framework.fs/fs,dir
    mysli.util.config
    mysli.web.assets
');

class __init
{
    private static $defaults;

    /**
     * Read the default settings
     */
    static function __init()
    {
        self::$defaults = ym::decode_file(
            fs::pkgreal('mysli.js.external', 'data/defaults.ym')
        );
    }

    /**
     * Get defaults
     */
    static function defaults()
    {
        return self::$defaults;
    }

    /**
     * Enable
     */
    static function enable()
    {
        $c = config::select('mysli.js.external');
        $c->reset([
            // Which scripts should be load locally.
            // Use curl to acquire scripts from a remote URL.
            'local' => [],
            // For which scripts development version should be used.
            'development' => []
        ]);

        return dir::create(assets::get_public_path('mysli.js.external'))
            && $c->save();
    }

    /**
     * Disable
     */
    static function disable()
    {
        return dir::remove(assets::get_public_path('mysli.js.external'))
            && config::select('mysli.js.external')->destroy();
    }
}
