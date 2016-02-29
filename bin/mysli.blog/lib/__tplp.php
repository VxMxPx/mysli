<?php

namespace mysli\blog; class __tplp
{
    const __use = <<<fin
    mysli.frontend.{ __tplp -> frontend.tplp }
    mysli.toolkit.{ config, route }
fin;


    /**
     * Generate post-map (pages+table_of_contents)!
     * --
     * @param array  $pages
     * @param array  $toc
     * @param string $slug    Post's slug
     * @param string $current Current page's QUID
     * @param string $type
     * --
     * @return array
     */
    static function map(array $pages, array $toc, $current=null, $type='ul')
    {
        if (!count($pages))
            return;

        $map = ["<$type>"];

        foreach ($pages as $pid => $page)
        {
            $url = static::url($page['fquid'], 'ppost');
            $class = $current === $page['quid'] ? 'current' : 'not-current';

            $map[] = "<li class=\"{$class}\">";
            $map[] = "<a href=\"{$url}\">{$page['title']}</a></li>";
            if (isset($toc[$page['quid']]))
            {
                $tocs = $toc[$page['quid']];
                array_shift($tocs); // Drop first title...
                $map[] = static::toc($tocs, $url, $type);
            }
            $map[] = '</li>';
        }

        $map[] = "</{$type}>";

        return implode("\n", $map);
    }

    /**
     * Output table of contents.
     * --
     * @param array  $toc
     * @param string $slug Post + Page slug for which TOC is being generated
     * @param string $type
     * --
     * @return string
     */
    static function toc(array $toc, $url=null, $type='ul')
    {
        if (!count($toc))
            return;

        $tocs = [];
        $tocs[] = "<{$type}>";

        foreach ($toc as $tid => $item)
        {
            $tocs[] = "<li>";
            $tocs[] = "<a href=\"{$url}#{$item['fid']}\">{$item['title']}</a></li>";

            if (count($item['items']))
            {
                $tocs[] = static::toc($item['items'], $url, $type);
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
