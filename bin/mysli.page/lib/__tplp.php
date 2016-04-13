<?php

namespace mysli\page; class __tplp
{
    const __use = <<<fin
    mysli.frontend.{ __tplp -> frontend.tplp }
    mysli.toolkit.{ route }
fin;

    /**
     * Return internal blog URL.
     * --
     * @param string $uri
     * --
     * @return string
     */
    static function url($uri='')
    {
        $url = route::to_url(
            "mysli.page.frontend::page",
            [ $uri ],
            false
        );

        return frontend\tplp::url($url);
    }
}
