<?php

namespace mysli\frontend; class __setup
{
    const __use = 'mysli.toolkit.{
        config,
        fs.fs -> fs,
        fs.dir -> dir,
        fs.file -> file
    }';

    static function enable()
    {
        return dir::create(fs::cntpath('themes/default')) &&
            file::write(
                fs::cntpath('themes/default/theme.ym'),
                'source: [ mysli.toolkit, assets/theme ]'
            );
    }

    static function disable()
    {
        return dir::remove(fs::cntpath('themes/default'));
    }
}
