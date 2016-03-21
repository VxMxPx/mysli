<?php

namespace mysli\content; class version
{
    const dir = '@versions';

    protected $iid;
    protected $cid;
    protected $language;

    function __construct($cid, $iid, $language)
    {
        $this->iid      = $iid;
        $this->cid      = $cid;
        $this->language = $language;
    }

    /**
     * Get path for a version file.
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
     * Generate version filename.
     * --
     * @return string
     */
    function filename($version, $hash)
    {
        return "v{$version}.{$this->language}.{$hash}.json";
    }

    /**
     * Get an array list of versions.
     * --
     * @return array
     *         [ version => [ version, filename, hash ] ]
     */
    function ls()
    {
        $path = $this->path();
        $versions = [];

        if (!dir::exists($path))
        {
            return $versions;
        }

        foreach (fs::ls($path) as $filename)
        {
            if (preg_match('#^v([0-9]+)\.([a-z_]+)\.(.*?).json$#', $filename, $match))
            {
                list($_, $version, $language, $hash) = $match;

                if ($language === $this->language)
                {
                    $versions[(int)$version] = [
                        'version'  => (int) $version,
                        'filename' => $filename,
                        'hash'     => $hash
                    ];
                }
            }
        }

        ksort($versions);
        return $versions;
    }

    /**
     * Return version meta information.
     * Last written version can be accessed with $id = -1, the one before with -2,
     * first version with $id = 1, etc...
     * --
     * @param integer $id version position
     * --
     * @return array
     */
    function meta($id=-1)
    {
        $versions = $this->ls();
        $version = array_slice($versions, $id, 1);
        return array_pop($versions);
    }

    /**
     * Write a new version of a post.
     * --
     * @param source  $source
     * @param boolean $force Write even if hash is the same as in previous version.
     * --
     * @return boolean
     */
    function up($source, $force=false)
    {
        $hash = $source->hash();
        $version = $this->meta(-1);

        if (!$force)
        {
            if (!empty($version) && $version['hash'] === $hash)
            {
                return true;
            }
        }

        $version = !empty($version) ? $version['version']+1 : 1;
        $path = $this->path($this->filename($version, $hash));

        if (!dir::exists($this->path()))
            dir::create($this->path());

        return json::encode_file($path, $source->all());
    }

    /**
     * Load sources of any version.
     * --
     * @param integer $version
     * --
     * @throws mysli\content\exception\version
     *         10 Cannot switch to version, not found.
     * --
     * @return boolean
     */
    function load($version)
    {
        $versions = $this->ls();

        if (!isset($versions[(int)$version])) return;

        $version = $versions[$version];
        $filename = $version['filename'];

        return json::decode_file($this->path($filename));
    }

    /**
     * Switch to particular version.
     * CAREFUL: this will rewrite source files!
     * --
     * @param array $sources
     */
    function switch($version)
    {
        $version = $this->load($version);

        foreach ($version['sources'] as $filename => $content)
            file::write(fs::cntpath($this->cid, $this->iid, $filename), $content);
    }
}
