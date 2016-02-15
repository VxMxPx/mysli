<?php

namespace mysli\page; class __setup
{
    const __use = <<<fin
        mysli.toolkit.{ config, route }
        mysli.toolkit.fs.{ fs, dir, file }
fin;

    static function enable()
    {
        $c = config::select('mysli.page');
        $c->init(
            [
                // Default locales code (the when file-code is absent).
                'locale.default'            => [ 'string', 'en' ],
                // All supported locales.
                'locale.support'            => [ 'array', [ 'en' ] ],
                // Reload cache if file change since last creation.
                'cache.reload-on-access'    => [ 'boolean', true ],
                // Re-publish media when cache is being re-loaded.
                'media.republish-on-reload' => [ 'boolean', true ],
                // Write version on cache reload.
                'version.up-on-reload'      => [ 'boolean', true ],
            ]
        );

        return

        // Save config
        $c->save()

        and

        // Create default pages directory
        dir::create(fs::cntpath('pages'))

        and

        // Create route to handle pages
        route::add('mysli.page.frontend::index', 'ANY', '*index', 'low') and
        route::add('mysli.page.frontend::page', 'ANY', '/<page:path>.html', 'low') and
        route::write()

        // Done
        ;
    }

    static function disable()
    {
        return !!route::remove('mysli.page.frontend::*');
    }
}
