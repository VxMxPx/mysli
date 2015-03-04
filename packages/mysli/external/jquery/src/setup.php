<?php

namespace mysli\external\jquery\setup;

__use(__namespace__, '
    mysli.framework.fs/dir
    mysli.util.config
    mysli.web.assets
');

function enable()
{
    $c = config::select('mysli.external.jquery');
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

    return dir::create(assets::get_public_path('mysli.external.jquery'))
        && $c->save();
}
function disable()
{
    return dir::remove(assets::get_public_path('mysli.external.jquery'))
        && config::select('mysli.external.jquery')->destroy();
}
