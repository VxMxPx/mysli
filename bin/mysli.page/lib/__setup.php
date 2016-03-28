<?php

namespace mysli\page; class __setup
{
    const __use = <<<fin
        mysli.toolkit.{ route }
        mysli.toolkit.fs.{ fs, dir, file }
fin;

    static function enable()
    {
        return

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
        route::remove('mysli.page.frontend::*');
        return route::write();
    }
}
