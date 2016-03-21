<?php

namespace mysli\content; class source
{
    protected $sources = [];
    protected $cid, $iid;

    function __construct($cid, $iid)
    {
        $this->cid = $cid;
        $this->iid = $iid;
    }

    /**
     * Get master hash for all files.
     * --
     * @return string
     */
    function hash()
    {
        // If files would be loaded by any other order,
        // we still desire the same hash.
        $sources = $this->sources;
        ksort($sources);

        $hash = '';

        foreach ($sources as $filename => $content)
            $hash .= md5($filename).md5($content);

        return md5($hash);
    }

    /**
     * Get filenames of all source files.
     * --
     * @return array
     */
    function files()
    {
        return array_keys($this->sources);
    }

    /**
     * Add source file to the list of sourcrs, and load it.
     * --
     * @param string $filename
     * @param string $soruce
     */
    function add($filename, $source)
    {
        $this->sources[$filename] = $source;
    }

    /**
     * Load and add source file.
     * --
     * @param  string $filename
     */
    function load($filename)
    {
        if ($this->exists($filename))
        {
            $contents = file::read(fs::cntpath($this->cid, $this->iid, $filename));
            $this->add($filename, $contents);
            return $contents;
        }
    }

    /**
     * Get particular source file.
     * --
     * @param string $filename
     * --
     * @return string
     */
    function get($filename)
    {
        if ($this->has($filename))
            return $this->sources[$filename];
    }

    /**
     * Get all added sources.
     * --
     * @return array
     */
    function all()
    {
        return $this->sources;
    }

    /**
     * Check weather particular source is loaded.
     * --
     * @param string $filename
     * --
     * @return boolean
     */
    function has($filename)
    {
        return isset($this->sources[$filename]);
    }

    /**
     * Check weather source exists in file system.
     * --
     * @param string $filename
     * --
     * @return boolean
     */
    function exists($filename)
    {
        return file::exists(fs::cntpath($this->cid, $this->iid, $filename));
    }
}
