<?php

namespace mysli\page; class frontend
{
    const __use = <<<fin
    .{ page }
    mysli.content.{ cache }
    mysli.frontend.{ frontend -> fe }
    mysli.toolkit.fs.{ fs, file, dir }
    mysli.toolkit.type.{ arr }
fin;

    /**
     * Render index page.
     * --
     * @return boolean
     */
    static function index()
    {
        return static::page('index');
    }

    /**
     * Render any page.
     * --
     * @param string $path
     * --
     * @return boolean
     */
    static function page($iid)
    {
        $language = '_def';

        // Not found, nothing to do
        if (!page::exists($iid, $language)) return false;

        $cache = new cache(page::cid, $iid, $language);

        // Has cached version?
        if ($cache->exists() && $cache->is_fresh())
        {
            $page = $cache->get();
        }
        else
        {
            $page = page::get($iid, $language);
            unset($page['.sources']);
            // Produce cache!
            $cache->write($page);
        }

        // Check publish status
        if (!arr::get($page, 'published', true))
        {
            // Final chance to view in dev-access
            if (!arr::get($page, 'dev-access')
                || request::get('access') !== arr::get($page, 'dev-access'))
            {
                return false;
            }
        }

        // Set template(s) to render
        $templates = ['page', ['mysli.page', 'page']];
        if (isset($page['template']))
        {
            array_unshift($templates, $page['template']);
        }

        // Merge with self
        $page = array_merge($page, $page['page']);
        unset($page['page']);

        // Render finally
        fe::render($templates, [
            'front' => [
                'subtitle' => arr::get($page, 'title', ''),
                'type'     => 'page',
                'quid'     => 'page-'.str_replace('_', '-', $page['fid'])
            ],
            'page' => $page
        ]);

        return true;
    }
}
