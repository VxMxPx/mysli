<?php

namespace mysli\content; class cache
{
    const __use = <<<fin
        .{ source }
        mysli.toolkit.{ json }
        mysli.toolkit.fs.{ fs, file, dir }
fin;

    const dir = '.cache';

    protected $cid, $iid, $language;

    /**
     * Construct new cache for particular post.
     * --
     * @param string $cid
     * @param string $iid
     * @param string $language
     */
    function __construct($cid, $iid, $language='_def')
    {
        $this->cid = $cid;
        $this->iid = $iid;
        $this->language = $language;
    }

    /**
     * Get unique filename for this item.
     * --
     * @return string
     */
    function filename()
    {
        $fid = preg_replace('/[^a-z0-9]/', '_', $this->iid);
        return "{$fid}{$this->language}.json";
    }

    /**
     * Full absolute path.
     * --
     * @param string $filename
     *        True for $this->filename()
     * --
     * @return string
     */
    function path($filename=null)
    {
        if ($filename === true) $filename = $this->filename();
        return fs::cntpath($this->cid, static::dir, $filename);
    }

    /**
     * Check weather has exists.
     * --
     * @return boolean
     */
    function exists()
    {
        return file::exists($this->path(true));
    }

    /**
     * Check weather cache is fresh.
     * --
     * @return boolean
     */
    function is_fresh()
    {
        if (!$this->exists()) return false;

        $post = $this->get();

        if (!isset($post['.includes']) || !isset($post['.hash'])) return false;

        $source = new source($this->cid, $this->iid, $this->language);

        foreach ($post['.includes'] as $include)
            $source->load($include);

        return $source->hash() === $post['.hash'];
    }

    /**
     * Remove cache file.
     * --
     * @return boolean
     */
    function remove()
    {
        if ($this->exists())
            return !! file::remove($this->path(true));

        return true;
    }

    /**
     * Get cached post.
     * --
     * @return array
     */
    function get()
    {
        return json::decode_file($this->path(true), true);
    }

    /**
     * Write (new) cache.
     * --
     * @return boolea
     */
    function write(array $post)
    {
        return json::encode_file($this->path(true), $post);
    }
}
