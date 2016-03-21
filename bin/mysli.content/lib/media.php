<?php

namespace mysli\content; class media
{
    const __use = <<<fin
        mysli.toolkit.fs.{ fs, file, dir }
fin;

    const dir = '@media';

    protected $iid;
    protected $cid;

    function __construct($cid, $iid)
    {
        $this->iid = $iid;
        $this->cid = $cid;
    }

    /**
     * Path to a media file.
     * --
     * @param string $file
     * --
     * @return string
     */
    function path($file=null)
    {
        return fs::cntpath($this->cid, $this->iid, static::dir, $file);
    }

    /**
     * Public path to a media file.
     * --
     * @param string $file
     * --
     * @return string
     */
    function pubpath($file=null)
    {
        return fs::pubpath($this->cid, $this->iid, 'media', $file);
    }

    /**
     * Make all media (files in media directory) publicly available.
     * --
     * @param string $file Publish only this particular file.
     * --
     * @return boolean
     */
    function publish($file=null)
    {
        $dir      = $file ? dir::name($file) : null;
        $selfpath = $this->path($file);
        $pubpath  = $this->pubpath($dir);

        if (!dir::create($pubpath)) return false;

        return $file
            ? file::copy($selfpath, $pubpath)
            : dir::copy($selfpath, $pubpath);
    }

    /**
     * Make all media NOT publicly available anymore.
     * --
     * @param string $file Un-publish only this particular file.
     * --
     * @return boolean
     */
    function unpublish($file=null)
    {
        $public = $this->pubpath($file);

        return $file
            ? (!file::exists($public) or file::remove($public))
            : (!dir::exists($public)  or dir::remove($public));
    }

    /**
     * Unpublish, publish files.
     * --
     * @param string $file
     * --
     * @return boolean
     */
    function refresh($file=null)
    {
        return
            static::unpublish($file)
        and static::publish($file);
    }

    /**
     * Get media URI.
     * --
     * @param string $asset
     * --
     * @return string
     */
    function uri($asset=null)
    {
        return '/'.$this->cid.'/'.$this->iid.'/media/'.$asset;
    }
}
