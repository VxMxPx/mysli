<?php

namespace mysli\blog; class __tplp
{
    const __use = <<<fin
    mysli.frontend.{ __tplp -> frontend.tplp }
    mysli.toolkit.{ config, route }
fin;

    /**
     * Output table of contents.
     * --
     * @param array $toc
     * --
     * @return string
     */
    static function toc(array $toc, $type='ul')
    {
        $tocs = [];
        $tocs[] = "<{$type}>";

        foreach ($toc as $tid => $item)
        {
            $tocs[] = "<li>";
            $tocs[] = "<a href=\"#{$item['fid']}\">{$item['title']}</a></li>";

            if (count($item['items']))
            {
                $tocs[] = static::toc($item['items'], $type);
            }

            $tocs[] = "</li>";
        }

        $tocs[] = "</{$type}>";

        return implode("\n", $tocs);
    }

    /**
     * Get list of categories.
     * --
     * @return array
     */
    static function categories()
    {
        return config::select('mysli.blog', 'tags.to-categories', []);
    }

    /**
     * Return internal blog URL.
     * --
     * @param string $uri
     * @param string $type URI type: post|ppost|tag|archive
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
