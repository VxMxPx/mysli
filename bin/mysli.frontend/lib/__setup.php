<?php

namespace mysli\frontend; class __setup
{
    const __use = <<<fin
        .{ theme }
        mysli.assets
        mysli.toolkit.{
            config
            route
            fs.fs   -> fs
            fs.dir  -> dir
            fs.file -> file
        }
fin;

    static function enable()
    {
        $c = config::select('mysli.frontend');
        $c->init(
            [
                // Where locales can be found:
                // subdomain - http://si.domain.tld/
                // segment   - http://domain.tld/si/
                // get       - http://domain.tld/?loc=si
                'locale.at'      => [ 'string', null ],
                // Default locale's ID
                'locale.default' => [ 'string', 'us' ],
                // Locale's URL ID to I18n File Code
                'locale.accept'  => [ 'array',  [ 'us' => 'en-us' ] ],
                // Currently selected theme
                'theme.active'   => [ 'string', 'mysli.frontend' ]
            ]
        );

        /*
        Return...
         */
        return

        // Add Route Which Will Handle 404
        route::add('mysli.frontend.route::error', 'ANY', '*error', 'low') and
        route::write()

        and

        assets::publish('mysli.frontend')

        and

        // Save config
        $c->save()

        // Done
        ;
    }

    static function disable()
    {
        // Unregister route
        route::remove('mysli.frontend.*');
        route::write();

        // Remove published assets
        assets::unpublish('mysli.frontend');


        // Drop Config
        config::select('mysli.frontend')->destroy();

        // Done
        return true;
    }
}
