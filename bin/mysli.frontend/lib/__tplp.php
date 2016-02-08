<?php

namespace mysli\frontend; class __tplp
{
    const __use = <<<fin
    mysli.toolkit.{ request }
fin;

    /**
     * Return full URL (possibly with laguage prefix!).
     * --
     * @param string $uri
     * --
     * @return string
     */
    static function url()
    {
        $uri = func_get_args();
        $uri = implode('/', $uri);
        // return request::url().'/'.ltrim($uri, '/');
        return '/'.ltrim($uri, '/');
    }
}
