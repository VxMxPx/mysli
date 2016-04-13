<?php

namespace mysli\blog; class blog
{
    const __use = <<<fin
        .{ processor }
        mysli.content.{ source, collection }
        mysli.toolkit.{ ym }
        mysli.toolkit.fs.{ fs, file, dir }
        mysli.toolkit.type.{ arr }
fin;

    const cid = 'blog';

    /**
     * Get all blog categories.
     * --
     * @return array
     */
    static function categories()
    {
        $categories = fs::cntpath(static::cid, 'categories.ym');

        if (file::exists($categories))
        {
            return ym::decode_file($categories);
        }
        else
        {
            return [];
        }
    }

    /**
     * Get all posts.
     * This will NOT process includes!
     * Slow, should be cached!
     * --
     * @return array
     */
    static function all()
    {
        return static::filter(null);
    }

    /**
     * Get all posts.
     * This will NOT process includes!
     * Slow, should be cached!
     * --
     * @param callable $call
     *        Function call for each item, if return false, item will not be
     *        included on a list.
     *        function ($meta) {}
     *        Null = no filter (the same as ::all)
     *
     * @param string $language
     *        List of posts of specific language.
     * --
     * @return array
     */
    static function filter($call, $language='_def')
    {
        return collection::filter(
            static::cid,
            function ($iid, $language) use ($call)
            {
                list($item) = processor::slice_source(

                    file::read(fs::cntpath(static::cid, $iid, "{$language}.post")),

                    function ($section, $position) {
                        if ($position === 0)
                        {
                            return ym::decode($section);
                        }
                        return false;
                    });

                $item['iid'] = $iid;
                $item['language'] = $language;
                $item = array_merge(static::get_defaults(), $item);
                $item['time'] = strtotime($item['date']);
                $item['year'] = gmdate('Y', $item['time']);

                return !$call || $call($item) !== false ? $item : false;

            }, $language);
    }

    /**
     * Get one blog post.
     * --
     * @param string $iid
     * @param string $language
     * --
     * @return post
     */
    static function get($iid, $language='_def')
    {
        // Not found, nothing to do
        if (!static::exists($iid, $language)) return;

        // Sources
        $sources = new source(static::cid, $iid);
        $sources->load("{$language}.post");

        // Slice main source file
        list($meta, $pages) = processor::slice_source(
            $sources->get("{$language}.post"),
            function ($section, $position) use ($iid)
            {
                // First section is always META
                if ($position === 0) return ym::decode($section);

                // Second section is actual MARKDOWN post
                if ($position === 1)
                {
                    return processor::body(
                        $section,
                        function ($parser) use ($iid)
                        {
                            // Add costume link handling to the parser for media files.
                            $link = $parser->get_processor('mysli.markdown.module.link');
                            $link->set_local_url(
                                '#^/(.*?)(?<!\.html|\.php|\.htm)$#',
                                '/'.static::cid.'/'.$iid);
                        });
                }

                // Any additional sections would be discarded
                return false;
            });

        // Find includes in meta
        $meta = processor::includes(
            $meta,
            function ($filename) use ($sources) {
                if ($sources->exists($filename))
                {
                    return ym::decode($sources->load($filename));
                }
            });

        // Construct post item and return it
        return array_merge(static::get_defaults(), $meta, [
            'iid'       => $iid,
            'fid'       => preg_replace('/[^a-z0-9]/', '_', $iid),
            'pages'     => $pages,
            '.sources'  => $sources,
            '.includes' => $sources->files(),
            '.hash'     => $sources->hash()
        ]);
    }

    /**
     * Check weather blog post exists.
     * --
     * @param string $iid
     * @param string $language
     * --
     * @return boolean
     */
    static function exists($iid, $language='_def')
    {
        return file::exists(fs::cntpath(static::cid, $iid, "{$language}.post"));
    }

    /**
     * Get settings array.
     * --
     * @return array
     */
    static function settings()
    {
        static $cache = null;

        if (!$cache)
            $cache = ym::decode_file(fs::cntpath(static::cid, 'settings.ym'));

        return $cache;
    }

    /**
     * Get default values for an item.
     * --
     * @return array
     */
    static function get_defaults()
    {
        return arr::get(static::settings(), 'default', []);
    }
}
