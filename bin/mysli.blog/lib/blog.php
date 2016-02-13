<?php

namespace mysli\blog; class blog
{
    const __use = <<<fin
    mysli.std.post
    mysli.toolkit.{ json }
    mysli.toolkit.fs.{ fs, file, dir }
    mysli.toolkit.type.{ str, arr }
fin;

    /**
     * Refresh posts list, and create cache!
     * --
     * @return boolean
     */
    static function refresh_list()
    {
        $root = fs::cntpath('blog');

        $posts = [];
        $tags = [];

        foreach (fs::ls($root) as $year)
        {
            if (!dir::exists(fs::ds($root, $year)))
            {
                continue;
            }

            foreach (fs::ls(fs::ds($root, $year)) as $slug)
            {
                if (substr($slug, -1) === '~') continue;
                if (substr($slug, 0, 1) === '.') continue;

                $quid = static::get_id($year, $slug);

                if (!$quid) continue;

                $post = new post('blog/'.$quid);
                $languages = $post->list_languages();
                $meta = $post->source('meta');
                $date = isset($meta['date']) ? strtotime($meta['date']) : time();

                $dtime = date('YmdHis', $date);
                $posts[$dtime.'_'.$quid] = [];

                if (!is_array($languages))
                {
                    continue;
                }

                foreach ($languages as $lngid)
                {
                    $lpost = new post('blog/'.$quid, $lngid);
                    $meta = $lpost->source('meta');
                    $ptags = arr::get($meta, 'tags', []);

                    if (!is_array($ptags))
                    {
                        \log::warning(
                            "Tags format is expected to be an array: `{$quid}`.",
                            __CLASS__
                        );
                        $ptags = [ $ptags ];
                    }

                    $posts[$dtime.'_'.$quid][$lngid] = [
                        'quid'      => $quid,
                        'title'     => arr::get($meta, 'title'),
                        'tags'      => $ptags,
                        'date'      => date('c', strtotime(arr::get($meta, 'date'))),
                        'year'      => date('Y', strtotime(arr::get($meta, 'date'))),
                        'published' => arr::get($meta, 'published', true),
                        'hash'      => $lpost->get_hash(true)
                    ];

                    // Add tag! :)
                    foreach ($ptags as $tag)
                    {
                        if (isset($tags[$tag]))
                        {
                            if (in_array($quid, $tags[$tag]))
                            {
                                continue;
                            }
                        }
                        else
                        {
                            $tags[$tag] = [];
                        }

                        $tags[$tag][] = $quid;
                    }
                }
            }
        }

        $cache_dir = fs::cntpath('blog/cache~');

        if (!dir::exists($cache_dir))
        {
            dir::create($cache_dir);
        }

        return json::encode_file(fs::ds($cache_dir, 'posts.json'), $posts)
            && json::encode_file(fs::ds($cache_dir, 'tags.json'), $tags);
    }

    /**
     * Get list of all posts. Can filter by language.
     * Cache must exists, as this will read from cached list!
     * --
     * @param string $language
     * --
     * @return array
     */
    static function list_all($language='_def')
    {
        $cache = fs::cntpath('blog/cache~/posts.json');

        if (!file::exists($cache))
        {
            return [];
        }
        else
        {
            $list = json::decode_file($cache, true);
        }

        $posts = [];

        foreach ($list as $quid => $post)
        {
            if (array_key_exists($language, $post))
            {
                $posts[$quid] = $post[$language];
            }
        }

        return $posts;
    }

    /**
     * Get list of posts per tag.
     * Cache must exists, as this will read from cached list!
     * --
     * @param string $tag
     * @param string $language
     * --
     * @return array
     */
    static function list_by_tag($tag, $language='_def')
    {
        $list = static::list_all($language);
        $posts = [];

        foreach ($list as $quid => $post)
        {
            if (in_array($tag, $post['tags']))
            {
                $posts[$quid] = $post;
            }
        }

        return $posts;
    }

    /**
     * Get one post by year+slug (e.g. 2015, blog-post).
     * --
     * @param integer $year
     * @param string  $path
     * @param string  $language
     * --
     * @return mysli\std\post\post
     */
    static function get_one($year, $slug, $language='_def')
    {
        if (!static::has($year, $slug, $language))
        {
            return false;
        }

        $id = static::get_id($year, $slug);

        return new post("blog/{$id}", $language);
    }

    /**
     * Check if particular post exists.
     * --
     * @param integer $year
     * @param string  $slug
     * @param string  $language
     * --
     * @return boolean
     */
    static function has($year, $slug, $language='_def')
    {
        $id = static::get_id($year, $slug);

        if (!$id) return false;

        return file::exists(fs::cntpath('blog', $id, "{$language}.md"));
    }

    /**
     * Year+slug to post ID, e.g. 2015, blog-post-slug => 2015/blog-post-slug
     * --
     * @param integer $year
     * @param string  $slug
     * --
     * @return string
     */
    static function get_id($year, $slug)
    {
        $year = (int) $year;
        $slug = str::clean($slug, 'slug');

        if (!$year) return false;

        return "{$year}/{$slug}";
    }
}
