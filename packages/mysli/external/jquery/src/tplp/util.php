<?php

namespace mysli\external\jquery\tplp;

__use(__namespace__, '
    ./jquery -> root\jquery
');

class util
{
    static function tag($version=null, $dev=null)
    {
        $link = self::link($version, $dev);
        return '<script src="'.$link.'"></script>';
    }
    static function link($version=null, $dev=null)
    {
        return root\jquery::get_link($version, $dev);
    }
}
