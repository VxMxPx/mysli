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

        $cache = new cache(blog::cid, $iid, $language);

        // Has cached version?
        if ($cache->exists())
        {
            if ($cache->is_fresh())
            {
                $post = $cache->get();
            }
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

        fe::render(['blog-post', ['mysli.blog', 'post']], [
            'front' => [
                'subtitle' => arr::get_deep($post, ['meta', 'title'], ''),
                'type'     => 'blog-post',
                'quid'     => 'post-'.$post['fid']
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
