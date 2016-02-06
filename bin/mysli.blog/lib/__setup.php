<?php

namespace mysli\blog; class __setup
{
    const __use = <<<fin
        mysli.toolkit.{ config, route }
        mysli.toolkit.fs.{ fs, dir, file }
fin;

    static function enable()
    {
        $c = config::select('mysli.blog');
        $c->init(
            [
                // Tags which supposed to be threated as categories
                'tags.to-categories'        => [ 'array', [] ],
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

        // Default directories and
        dir::create(fs::cntpath('blog'))

        and

        // Add Routes
        route::add(
            'mysli.blog.frontend::archive',
            'ANY',
            '/r',
            'medium')

        and

        route::add(
            'mysli.blog.frontend::post',
            'ANY',
            '/r/<year:digit>/<post:slug>.html',
            'medium')

        and

        route::add(
            'mysli.blog.frontend::tag',
            'ANY',
            '/r/tag/<tag:slug>/',
            'medium')

        and

        route::write()

        // Done
        ;
    }

    static function disable()
    {
        // Drop Config
        config::select('mysli.blog')->destroy();

        return

        // Remove default route
        !!route::remove('mysli.blog.frontend::*')

        // Done
        ;
    }
}
