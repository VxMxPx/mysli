<?php

namespace mysli\blog; class frontend
{
    const __use = <<<fin
    .{ blog }
    mysli.content.{ cache }
    mysli.i18n
    mysli.toolkit.{ request, json }
    mysli.toolkit.fs.{ fs, file, dir }
    mysli.toolkit.type.{ arr }
    mysli.frontend.{ frontend -> fe }
fin;

    /**
     * Get RSS output.
     * --
     * @return boolean
     */
    static function feed()
    {
        $settings = blog::settings();
        $cache_filename = fs::cntpath(blog::cid, cache::dir, '_list_feed.json');

        // Cache
        if (file::exists($cache_filename))
        {
            $list = json::decode_file($cache_filename, true);
        }
        else
        {
            $list = blog::all();

            // Sort by date!
            uasort($list, function ($a, $b) {
                $a = strtotime($a['date']);
                $b = strtotime($b['date']);
                if ($a === $b) return 0;
                return ($a > $b) ? -1 : 1;
            });

            // Limit number of posts
            $list = array_slice(
                $list,
                0,
                (int) arr::get_deep($settings, ['feed', 'limit'], 20));

            foreach ($list as $iid => &$post)
            {
                $post = blog::get($iid, '_def', request::url());
                reset($post['pages']);
                $page = key($post['pages']);
                if (isset($post['pages'][$page])) $post['page'] = $post['pages'][$page];
                unset($post['pages']);
            }

            json::encode_file($cache_filename, $list);
        }

        // Get updated timestamp
        $last = arr::first($list);
        $updated = date('c', strtotime(arr::get($last, 'date', time())));

        fe::render(['blog-feed', ['mysli.blog', 'feed']], [
            'front' => [
                'subtitle' => arr::get_deep($settings, ['feed', 'title'], ''),
                'type'     => 'blog-feed'
            ],
            'feed' => [
                'title'   => arr::get_deep($settings, ['feed', 'title'], ''),
                'updated' => $updated,
            ],
            'blog' => [
                'categories' => blog::categories(),
                'is_archive' => false,
                'is_tag'     => false,
                'tag'        => null,
            ],
            'posts' => $list
        ]);

        return true;
    }

    /**
     * Render blog archive.
     * --
     * @return boolean
     */
    static function archive()
    {
        $cache_filename = fs::cntpath(blog::cid, cache::dir, '_list_archive.json');

        // Cache
        if (file::exists($cache_filename))
        {
            $list = json::decode_file($cache_filename, true);
        }
        else
        {
            $list = blog::all();

            // Sort by date!
            uasort($list, function ($a, $b) {
                $a = strtotime($a['date']);
                $b = strtotime($b['date']);
                if ($a === $b) return 0;
                return ($a > $b) ? -1 : 1;
            });

            json::encode_file($cache_filename, $list);
        }

        fe::render(['blog-archive', ['mysli.blog', 'archive']], [
            'front' => [
                'subtitle' => i18n::select(['mysli.blog', 'en', null], 'ARCHIVE'),
                'type'     => 'blog-archive'
            ],
            'blog' => [
                'categories' => blog::categories(),
                'is_archive' => true,
                'is_tag'     => false,
                'tag'        => null,
            ],
            'posts' => $list
        ]);

        return true;
    }

    /**
     * Render archive for a particular tag.
     * --
     * @param string $id
     * --
     * @return boolean
     */
    static function tag($id)
    {
        $cache_filename = fs::cntpath(
            blog::cid, cache::dir, "_list_tag_{$id}.json");

        // Cache
        if (file::exists($cache_filename))
        {
            $list = json::decode_file($cache_filename, true);
        }
        else
        {
            $list = blog::filter(function ($item) use ($id) {
                if (!in_array($id, $item['tags'])) return false;
            });

            // Sort by date!
            uasort($list, function ($a, $b) {
                $a = strtotime($a['date']);
                $b = strtotime($b['date']);
                if ($a === $b) return 0;
                return ($a > $b) ? -1 : 1;
            });

            json::encode_file($cache_filename, $list);
        }

        fe::render(['blog-archive', ['mysli.blog', 'archive']], [
            'front' => [
                'subtitle' => ucfirst($id),
                'type'     => 'blog-tag',
                'quid'     => 'blog-tag-'.$id,
            ],
            'blog' => [
                'categories' => blog::categories(),
                'is_archive' => false,
                'is_tag'     => true,
                'tag'        => $id
            ],
            'posts' => $list
        ]);

        return true;
    }

    /**
     * Render a particular page of a post.
     * --
     * @param integer $year
     * @param string  $id
     * @param string  $page
     * --
     * @return boolean
     */
    static function ppost($year, $id, $page)
    {
        return static::post($year, $id, $page);
    }

    /**
     * Render a particular post.
     * --
     * @param integer $year
     * @param string  $id
     * @param string  $page
     * --
     * @return boolean
     */
    static function post($year, $id, $page='_default')
    {
        $iid = "{$year}/{$id}";
        $language = '_def';

        // Not found, nothing to do
        if (!blog::exists($iid, $language)) return false;

        $cache = new cache(blog::cid, $iid, $language);

        // Has cached version?
        if ($cache->exists() && $cache->is_fresh())
        {
            $post = $cache->get();
        }
        else
        {
            $post = blog::get($iid, $language);
            unset($post['.sources']);
            // Produce cache!
            $cache->write($post);
        }

        // Check publish status
        if (!arr::get($post, 'published', true))
        {
            // Final chance to view in dev-access
            if (!arr::get($post, 'dev-access')
                || request::get('access') !== arr::get($post, 'dev-access'))
            {
                return false;
            }
        }

        // Default page, multiple pages...
        if ($page === '_default' && count($post['pages']) > 1)
        {
            reset($post['pages']);
            $page = key($post['pages']);
        }

        if (!isset($post['pages'][$page]))
        {
            return false;
        }
        else
        {
            $post['page'] = $post['pages'][$page];
        }

        // Set template(s) to render
        $templates = ['blog-post', ['mysli.blog', 'post']];
        if (isset($post['template']))
        {
            array_unshift($templates, $post['template']);
        }

        fe::render($templates, [
            'front' => [
                'subtitle' => arr::get($post, 'title', ''),
                'type'     => 'blog-post',
                'quid'     => 'post-'.str_replace('_', '-', $post['fid'])
            ],
            'blog' => [
                'categories' => blog::categories(),
                'is_archive' => false,
                'is_tag'     => false,
                'tag'        => null,
            ],
            'post' => $post
        ]);

        return true;
    }
}
