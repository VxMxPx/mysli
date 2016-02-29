<?php

namespace mysli\page; class page
{
    const __use = <<<fin
    mysli.std.post.{ post }
    mysli.toolkit.fs.{ fs, file, dir }
fin;

    /**
     * Get one page by path (e.g. about/me).
     * --
     * @param string $path
     * --
     * @return mysli\page\page
     */
    static function by_path($path)
    {
        if (!static::has($path))
        {
            return false;
        }

        $id = static::page_to_id($path);

        return new post("pages/{$id}");
    }

    /**
     * Check if particular page exists.
     * --
     * @param string $path
     * @param string $language
     * --
     * @return boolean
     */
    static function has($path, $language='_def')
    {
        $id = static::page_to_id($path);

        if (!$id) return false;

        return file::exists(fs::cntpath('pages', $id, "{$language}.md"));
    }

    /**
     * Convert raw page (e.g. about/me) to an ID.
     * --
     * @param string $page
     * --
     * @return string
     */
    static private function page_to_id($page)
    {
        if (strpos($page, '.') !== false) { return false; }
        return $page;
        // return str_replace('/', '.', $page);
    }
}
