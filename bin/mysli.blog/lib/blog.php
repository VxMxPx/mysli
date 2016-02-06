<?php

namespace mysli\blog; class blog
{
    const __use = <<<fin
    mysli.std.post
    mysli.toolkit.fs.{ fs, file, dir }
    mysli.toolkit.type.{ str }
fin;

    /**
     * Refresh posts list.
     */
    static function refresh_list()
    {

    }

    /**
     * Get list of all posts.
     * --
     * @return array
     */
    static function list_all() {}

    /**
     * Get list of posts per tag.
     * --
     * @param string $tag
     * --
     * @return array
     */
    static function list_by_tag($tag) {}

    /**
     * Get one post by year+slug (e.g. 2015, blog-post).
     * --
     * @param integer $year
     * @param string  $path
     * --
     * @return mysli\std\post\post
     */
    static function get_one($year, $slug)
    {
        if (!static::has($year, $slug))
        {
            return false;
        }

        $id = static::get_id($year, $slug);

        return new post("blog/{$id}", 'post');
    }

    /**
     * Check if particular post exists.
     * --
     * @param integer $year
     * @param string  $slug
     * --
     * @return boolean
     */
    static function has($year, $slug)
    {
        $id = static::get_id($year, $slug);

        if (!$id) return false;

        return file::exists(fs::cntpath('blog', $id, 'post.md'));
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
