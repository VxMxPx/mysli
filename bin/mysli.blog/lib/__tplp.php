<?php

namespace mysli\blog; class __tplp
{
    const __use = <<<fin
    mysli.frontend.{ __tplp -> frontend.tplp }
    mysli.toolkit.{ route }
fin;

    /**
     * Return internal blog URL.
     * --
     * @param string $uri
     * @param string $type URI type: post|tag|archive
     * --
     * @return string
     */
    static function url($uri='', $type='post')
    {
        $url = route::to_url(
            "mysli.blog.frontend::{$type}",
            explode('/', $uri),
            false
        );
        return frontend\tplp::url($url);
    }
}
