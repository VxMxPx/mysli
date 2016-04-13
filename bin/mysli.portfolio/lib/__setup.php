<?php

namespace mysli\portfolio; class __setup
{
    const __use = <<<fin
        mysli.toolkit.{ route }
        mysli.toolkit.fs.{ fs, dir, file }
fin;

    static function enable()
    {
        return
            // create porfolio directory
            dir::create(fs::cntpath('portfolio'))

            // create portfolio routes
            and route::add('mysli.portfolio.frontend::archive', 'ANY', '/portfolio', 'medium')
            and route::write();
    }

    static function disable()
    {
        return
            route::remove('mysli.portfolio.frontend::*')
            and route::write();
    }
}
