<?php

namespace mysli\frontend; class __tplp
{
    const __use = <<<fin
    mysli.toolkit.{ request }
    mysli.toolkit.type.{ arr }
fin;

    /**
     * Return full URL (possibly with laguage prefix!).
     * --
     * @param string  $... Path
     * @param boolean $absolute Weather absolute URL (inc. domain) should be retruned.
     * --
     * @return string
     */
    static function url()
    {
        $path = func_get_args();

        if (is_bool(arr::last($path)))
        {
            $absolute = array_pop($path);
        } else $absolute = false;

        $path = implode('/', $path);
        // $path = rtrim($path, '/').'/';

        if ($absolute)
        {
            return request::url().'/'.ltrim($path, '/');
        }
        else
        {
            return '/'.ltrim($path, '/');
        }
    }
}
