<?php

namespace mysli\std\post; class post
{
    const __use = <<<fin
    .{ exception.post }
    mysli.markdown.{ markdown, parser }
    mysli.toolkit.{ json, ym }
    mysli.toolkit.fs.{ fs, file, dir }
fin;

    /**
     * Unique post's ID.
     * --
     * @var string
     */
    protected $quid;

    /**
     * Post's full absolute path.
     * --
     * @var string
     */
    protected $path;

    /**
     * Post's filename (no extension).
     * --
     * @var string
     */
    protected $filename;

    /**
     * Post's language, e.g. `si`.
     * --
     * @var string
     */
    protected $language;

    /**
     * Language appended to file, e.g.: `post.si` in case of default just `post`.
     * --
     * @var string
     */
    protected $lfile;

    /**
     * Meta array.
     * --
     * @var array
     */
    protected $meta = null;

    /**
     * HTML version of post.
     * --
     * @var string
     */
    protected $html = null;

    /**
     * Source version of post.
     * --
     * @var string
     */
    protected $source = null;

    /**
     * Construct new Std Post.
     * --
     * @param string $path     Relative post's path (e.g. blog/2015/blog-post)
     * @param string $filename Source filename.
     * @param array  $language Post's language, [ desired, default ]
     * --
     * @throws mysli\std\post\exception\post 10 No post with such language.
     */
    function __construct(
        $path, $filename='post', array $language=[])
    {
        $this->quid     = $path;
        $this->path     = fs::cntpath($path);
        $this->filename = $filename;
        $this->language = $this->set_language($language);

        // Set lfile
        $this->lfile = $this->language
            ? $this->filename.".{$this->language}"
            : $this->filename;

        // Language is specifically `false` meaning file was not found, error!
        if ($this->language === false)
        {
            throw new exception\post(
                "Post not found: `{$path}`, language: `".
                implode(',', $language)."`.", 10);
        }

        // Make dirs if not there
        dir::create(fs::ds($this->path, 'cache~'));
        dir::create(fs::ds($this->path, 'versions'));
    }

    /**
     * Get unique post ID.
     * --
     * @return string
     */
    function get_quid()
    {
        return $this->quid;
    }

    /**
     * Get current post's cache ID.
     * --
     * @param boolean $fresh Generate fresh ID.
     * --
     * @return string
     */
    function get_cache_id($fresh=false)
    {
        if (!$fresh && file::exists(fs::ds($this->path, 'cache~/hash.json')))
        {
            $hashes = json::decode_file(
                fs::ds($this->path, 'cache~/hash.json'), true);
            $hlang = $this->language ? $this->language : 0;

            if (isset($hashes[$hlang]))
            {
                return $hashes[$hlang];
            }
            // Else pass through and get fresh cache!
        }

        return md5_file(fs::ds($this->path, $this->lfile.'.md'));
    }

    /**
     * Weather cache for this post exists.
     * --
     * @return boolean
     */
    function has_cache()
    {
        return
            dir::exists(fs::ds($this->path, 'cache~'))
        && file::exists(fs::ds($this->path, 'cache~/hash.json'))
        && file::exists(fs::ds($this->path, 'cache~', $this->lfile.'.html'))
        && file::exists(fs::ds($this->path, 'cache~', 'meta_'.$this->lfile.'.json'));
    }

    /**
     * Check weather version of cache is fresh.
     * --
     * @return boolean
     */
    function is_cache_fresh()
    {
        if (!$this->has_cache())
        {
            return false;
        }

        $hashes = json::decode_file(
            fs::ds($this->path, 'cache~/hash.json'), true);

        $hlang = $this->language ? $this->language : 0;

        if (!isset($hashes[$hlang]))
        {
            return false;
        }

        $this_hash = md5_file(fs::ds($this->path, $this->lfile.'.md'));

        if ($hashes[$hlang] !== $this_hash)
        {
            return false;
        }

        return true;
    }

    /**
     * Create fresh cache for this particular post.
     * --
     * @param string $language
     *        Refresh cache for particular language (false for current!)
     * --
     * @throws mysli\std\post\exception\post 10 File not found.
     * --
     * @return boolean
     */
    function refresh_cache($language=false)
    {
        if ($language === false)
        {
            $language = $this->language;
        }

        $lfile = $this->filename.($language?".{$language}":'');

        $source_file = fs::ds($this->path, $lfile.'.md');
        $cache_file  = fs::ds($this->path, 'cache~', $lfile.'.html');
        $meta_file   = fs::ds($this->path, 'cache~', "meta_{$lfile}.json");
        $hashes_file = fs::ds($this->path, 'cache~/hash.json');

        if (!file::exists($source_file))
        {
            throw new exception\post("File not found: `{$lfile}.md`.", 10);
        }

        if (file::exists($hashes_file))
        {
            $hashes = json::decode_file(
                fs::ds($this->path, 'cache~/hash.json'), true);
        }
        else
        {
            $hashes = [];
        }

        $hash = md5_file($source_file);
        $hashes[($language?$language:0)] = $hash;
        $html = $this->html( true );
        $meta = $this->meta( true );

        return
            // Write HASHES
            json::encode_file($hashes_file, $hashes)
        and // Write post's HTML
            file::write($cache_file, $html)
        and // Write post's META
            json::encode_file($meta_file, $meta);
    }

    /**
     * Get all available languages of this post.
     * Please note that the default language will be added as `null`, hence
     * the result could look like this:
     * [ file.md => null, file.si.md => si, file.ru.md => ru, ... ]
     * --
     * @return array
     */
    function list_languages()
    {
        $lngs = [];

        foreach (fs::ls($this->path, '*.md') as $file)
        {
            $lang = explode('.', $file, 3);
            $lang = count($lang) !== 3 ? null : $lang[1];
            $lngs[$file] = $lang;
        }

        return $lngs;
    }

    /**
     * Return current version (number).
     * --
     * @return integer
     */
    function get_current_version()
    {
        return $this->count_versions(false)+1;
    }

    /**
     * Number of written versions so far.
     * --
     * @param string $language
     *        For particular language, if false, current language will be used.
     * --
     * @return integer
     */
    function count_versions($language=false)
    {
        $versions = $this->list_versions($language);
        return empty($versions) ? 0 : array_pop($versions);
    }

    /**
     * Get an array list of versions.
     * --
     * @param string $language
     *        For particular language, if false, current language will be used.
     * --
     * @return array
     */
    function list_versions($language=false)
    {
        $path = fs::ds($this->path, 'versions');
        $versions = [];

        if (!dir::exists($path))
        {
            return $versions;
        }

        $language = $language !== false ? $language : $this->language;
        $lfile    = $language ? "{$this->filename}.{$language}" : $this->filename;
        $filter   = $language ? "*.{$lfile}.md" : '*.md';

        foreach (fs::ls($path, $filter) as $version)
        {
            $versions[] = (int) substr($version, 1, strpos($version, '.'));
        }

        sort($versions);
        return $versions;
    }

    /**
     * Write a new version of this post.
     * --
     * @return boolean
     */
    function new_version()
    {
        $version = $this->get_current_version();

        return file::write(
            fs::ds($this->path, 'versions', "v{$version}.{$this->lfile}.md"),
            $this->source()
        );
    }

    /**
     * Downgrade current post!
     * --
     * @param integer $to
     * @param string  $language False for current language.
     * --
     * @return boolean
     */
    function to_version($to, $language=false)
    {
        $vcontents = $this->get_version_source($to, $language);

        if (!$vcontents)
        {
            return false;
        }

        return $this->write_source($vcontents);
    }

    /**
     * Read particular version of post.
     * --
     * @param integer $version
     * @param string  $language False for current language.
     * --
     * @return string
     */
    function get_version_source($version, $language=false)
    {
        $language = $language === false ? $this->language : $language;
        $vfile = $language
            ? "v{$version}.{$this->filename}.{$language}.md"
            : "v{$version}.{$this->filename}.md";

        if (file::exists($vfile))
        {
            return file::read($vfile);
        }
        else
        {
            return null;
        }
    }


    /**
     * Make all media (files in media directory) publicly available.
     */
    function publish_media()
    {
        $this_media = fs::ds($this->path, 'media');

        if (dir::exists($this_media))
        {
            $public_media = fs::pubpath($this->quid, 'media');
            dir::create($public_media);
            dir::copy($this_media, $public_media);
        }
    }

    /**
     * Make all media not publicly available anymore.
     */
    function unpublish_media()
    {
        $public_media = fs::pubpath($this->quid);

        if (dir::exists($public_media))
        {
            dir::remove($public_media);
        }
    }

    /**
     * Get media URI.
     * --
     * @param string $asset
     * --
     * @return string
     */
    function media_uri($asset=null)
    {
        return $this->quid.'/media/'.$asset;
    }

    /**
     * Get particular key from meta.
     * --
     * @param string $key
     * @param mixed  $default
     * --
     * @return mixed
     */
    function get($key, $default=null)
    {
        $meta = $this->meta();

        if (!array_key_exists($key, $meta))
        {
            return $default;
        }
        else
        {
            return $meta[$key];
        }
    }

    /**
     * Set particular meta key.
     * --
     * @param string $key
     * @param mixed $value
     */
    function set($key, $value)
    {
        // Load meta if not there
        $this->meta();
        $this->meta[$key] = $value;
    }


    /**
     * Get meta data for page.
     * --
     * @param boolean $fresh
     *        Grab fresh meta from `post.md` rather than reading cached version.
     *        This will loose all set values if not written.
     * --
     * @return array
     */
    function meta($fresh=false)
    {
        if ($this->meta === null || $fresh)
        {
            $this->meta = $this->meta_for($this->language, $fresh);
        }

        return $this->meta;
    }

    /**
     * Write current meta to source file.
     * --
     * @return boolean
     */
    function write_meta()
    {
        return $this->write_source( $this->source(), $this->meta() );
    }

    /**
     * Get source version of the page.
     * --
     * @param string $language
     * --
     * @return string
     */
    function source($language=false)
    {
        if ($language !== false)
        {
            $lfile = $language ? "{$this->filename}.{$language}" : $this->filename;
            $filename = fs::ds($this->path, "{$lfile}.md");

            if (file::exists($filename))
            {
                return file::read($filename);
            }
            else
            {
                return null;
            }
        }

        if ($this->source === null)
        {
            $this->source = file::read(fs::ds($this->path, "{$this->lfile}.md"));
        }

        return $this->source;
    }

    /**
     * Write source to file.
     * --
     * @param string $source
     *
     * @param array  $meta
     *        If meta is absent it will be assumed it's included in source
     *        as a string and hence only source will be written.
     * --
     * @return boolean
     */
    function write_source($source, array $meta=null)
    {
        if ($meta && is_array($meta))
        {
            $meta = ym::encode($meta);
            $source = $meta."\n".str_repeat('=', 80)."\n".$source;
        }

        return !! file::write(
            fs::ds($this->path, $this->lfile.'.md'),
            $source."\n"
        );
    }

    /**
     * Get HTML version of the page.
     * --
     * @param boolean $fresh
     *        Grab fresh html from `post.md` rather than reading cached version.
     * --
     * @return string
     */
    function html($fresh=false)
    {
        if ($this->html === null || $fresh)
        {
            $filename = fs::ds($this->path, "cache~/{$this->lfile}.html");

            if (file::exists($filename) && !$fresh)
            {
                $this->html = file::read($filename);
            }
            else
            {
                $source = $this->source();
                $source = preg_split('/^\={3,}$/m', $source, 2);
                $page = trim($source[1]);

                $this->html = $this->process($page, $this->meta());
            }
        }

        return $this->html;
    }

    /**
     * Return meta + page as an array.
     * --
     * @return array
     */
    function as_array()
    {
        $meta = $this->meta();
        $meta['body'] = $this->html();
        $meta['language'] = $this->language;
        return $meta;
    }

    /**
     * Grab meta for particular language.
     * --
     * @param string $language
     *
     * @param boolean $fresh
     *        Grab fresh meta from `post.md` rather than reading cached version.
     *        This will loose all set values if not written.
     *
     * @param array $extended
     *        Internal usage, prevent infinite loop when extending.
     * --
     * @return array
     */
    protected function meta_for($language, $fresh=false, array $extended=[])
    {
        $lfile = $language ? "{$this->filename}.{$language}" : $this->filename;
        $meta_file = fs::ds($this->path, "cache~/meta_{$lfile}.json");

        if (file::exists($meta_file) && !$fresh)
        {
            return json::decode_file($meta_file, true);
        }
        else
        {
            $source = $this->source($language);
            $source = preg_split('/^\={3,}$/m', $source, 2);
            $meta = trim($source[0]);
            $meta = ym::decode($meta);

            // Need to extend?
            if (array_key_exists('extend', $meta))
            {
                $extended[] = $language ? $language : '%default';

                if (in_array($meta['extend'], $extended))
                {
                    \log::warning(
                        "Meta is extended multiple times in `{$this->quid}`, ".
                        "`{$lfile}`, list: `".implode(',', $extended)."`",
                        __CLASS__
                    );

                    return $meta;
                }

                $emeta = $this->meta_for($meta['extend'], $true, $extended);

                if (is_array($emeta))
                {
                    $meta = array_merge($emeta, $meta);
                }
            }

            return $meta;
        }
    }

    /**
     * Look through languages array and return the language for which the
     * source file exists.
     * --
     * @param array $languages
     * --
     * @return string
     *         False if not found in any form.
     */
    protected function set_language(array $languages)
    {
        if (empty($languages))
        {
            $languages = [ null ];
        }

        foreach ($languages as $language)
        {
            $ext = $language ? ".{$language}.md" : ".md";

            if (file::exists(fs::ds($this->path, $this->filename.$ext)))
            {
                return $language;
            }
        }

        return false;
    }

    /**
     * Process.
     * --
     * @param string $page
     * --
     * @return string
     */
    protected function process($page, array $meta)
    {
        if (!isset($meta['processor'])
            || $meta['processor'] === 'mysli.markdown.markdown::process')
        {
            $parser = new parser($page);
            $link = $parser->get_processor('mysli.markdown.module.link');
            $link->set_local_url('#^/(.*?)(?<!\.html|\.php|\.htm)$#', '/'.$this->quid);
            return markdown::process($parser);
        }
        else
        {
            $processor = '\\'.str_replace('.', '\\', $processor);

            if (is_callable($processor))
            {
                return call_user_func_array($processor, [ $page ]);
            }
            else
            {
                return false;
            }
        }
    }
}
