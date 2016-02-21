<?php

namespace mysli\std\post; class post
{
    const __use = <<<fin
    .{ exception.post }
    mysli.markdown.{ markdown, parser }
    mysli.toolkit.{ json, ym }
    mysli.toolkit.fs.{ fs, file, dir }
    mysli.toolkit.type.{ arr }
fin;

    /**
     * Unique post ID (e.g. blog/2016/post-slug)
     * --
     * @var string
     */
    protected $quid;

    /**
     * Post's language.
     * --
     * @var string
     */
    protected $language;

    /**
     * Post's full absolute path.
     * --
     * @var string
     */
    protected $path;

    /**
     * Post's META array.
     * --
     * @var array
     */
    protected $meta;

    /**
     * Post's HTML array (containing multiple pages or _default).
     * --
     * @var array
     */
    protected $html;

    /**
     * Post's source [ meta, source ]
     * --
     * @var array
     */
    protected $source;

    /**
     * New Std Post.
     * --
     * @param string $quid
     *        E.g. blog/2016/blog-slug
     *
     * @param string $language
     *        Create post instance of particular language.
     *        Language file must exists.
     */
    function __construct($quid, $language='_def')
    {
        $this->quid = $quid;
        $this->language = $language;
        $this->path = fs::cntpath($quid);

        if (!file::exists(fs::ds($this->path, "{$language}.md")))
        {
            throw new exception\post(
                "Post not found: `{$this->quid}`, language: `{$language}`.", 10);
        }

        $this->reset();

        // Make DIR if not there
        dir::create(fs::ds($this->path, 'cache~'));
        dir::create(fs::ds($this->path, 'versions'));
    }

    /**
     * Get unique post's ID (e.g. blog/2016/blog-slug)
     * --
     * @return string
     */
    function get_quid()
    {
        return $this->quid;
    }

    /**
     * Switch post's language.
     * --
     * @param string $language
     * --
     * @throws mysli\std\post\exception\post 10 Post not found.
     * --
     * @return boolean
     */
    function switch_language($language)
    {
        if ($language === $this->language)
        {
            return true;
        }

        if (!file::exists(fs::ds($this->path, "{$language}.md")))
        {
            throw new exception\post(
                "Post not found: `{$this->quid}`, language: `{$language}`.", 10);
        }

        $this->reset();
        $this->language = $language;
        return true;
    }

    /**
     * Reset loaded data.
     */
    function reset()
    {
        $this->meta = $this->html = $this->source = null;
    }

    /**
     * Get meta. There are different variations of meta depending on a page,
     * mostly sitemap and references will change, so if post has multiple pages,
     * then specify the desired page.
     * --
     * @param  string $page
     * --
     * @return array
     */
    function meta($page='_default')
    {
        if (!$this->meta)
        {
            if ($this->has_cache())
            {
                $cachef = fs::ds($this->path, "cache~/{$this->language}.json");
                $this->meta = json::decode_file($cachef, true);
            }
            else
            {
                $this->process();
            }
        }

        if (!$page)
        {
            return $this->meta;
        }

        // Set required page
        $page = $page === '_default' ? $this->get_first_page_id() : $page;
        $meta = $this->meta;

        if (isset($this->meta['pages'][$page]))
        {
            $meta['page'] = $this->meta['pages'][$page];

            foreach (['table_of_contents', 'references'] as $key)
            {
                $meta['page'][$key] = (isset($this->meta[$key])
                    && isset($this->meta[$key][$page]))
                        ? $this->meta[$key][$page]
                        : [];
            }
        }

        return $meta;
    }

    /**
     * Return processed final version of HTML.
     * --
     * @param string $page If multiple pages, specify particular page.
     * --
     * @return string
     */
    function html($page='_default')
    {
        if (!$this->html)
        {
            if ($this->has_cache())
            {
                $cachef = fs::ds($this->path, "cache~/{$this->language}.html");
                $html = file::read($cachef);
                $html = preg_split(
                    '/^===\/([a-z0-9_\-]+)\/={12}$/m', $html, -1,
                    PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);

                $this->html = [];
                $lastk = null;
                $meta = $this->meta(null);

                foreach ($html as $item)
                {
                    if (!$lastk)
                    {
                        if (isset($meta['pages'][$item]))
                        {
                            $lastk = $item;
                        }
                        else
                        {
                            throw new exception\post(
                                "No such page: `{$item}`", 10);
                        }
                    }
                    else
                    {
                        $this->html[$lastk] = trim($item);
                        $lastk = null;
                    }
                }
            }
            else
            {
                $this->process();
            }
        }

        if (!$page)
        {
            return $this->html;
        }

        $page = $page === '_default' ? $this->get_first_page_id() : $page;

        if (isset($this->html[$page]))
        {
            return $this->html[$page];
        }
        else
        {
            throw new exception\post(
                "No such page: `{$page}`, post: `{$this->quid}`.", 10);
        }
    }

    /**
     * Return ID of the first page (_default if only one)
     * --
     * @return string
     */
    function get_first_page_id()
    {
        $meta = $this->meta(null);
        $pages = $meta['pages'];
        reset($pages);
        return key($pages);
    }

    /**
     * Get post's unqiue HASH.
     * --
     * @param boolean $fresh
     * --
     * @return string
     */
    function get_hash($fresh)
    {
        if (!$fresh)
        {
            return $this->get('__hash', null);
        }
        else
        {
            return hash_file('md4', fs::ds($this->path, "{$this->language}.md"));
        }
    }

    /**
     * Check weather cache exists.
     * --
     * @return boolean
     */
    function has_cache()
    {
        return
            dir::exists(fs::ds($this->path, 'cache~'))
        && file::exists(fs::ds($this->path, "cache~/{$this->language}.json"))
        && file::exists(fs::ds($this->path, "cache~/{$this->language}.html"));
    }

    /**
     * Check weather version of cache is fresh.
     * --
     * @return boolean
     */
    function is_cache_fresh()
    {
        // Get cached hash !== get current hash!
        return $this->get_hash(false) === $this->get_hash(true);
    }

    /**
     * Produce a new version of hash!
     * --
     * @return boolean
     */
    function make_cache()
    {
        // Up to fresh files
        $this->load_source();
        $this->process();

        $cache = fs::ds($this->path, 'cache~');

        $html = '';

        foreach ($this->html as $index => $page)
        {
            $html .= "===/{$index}/============\n";
            $html .= $page."\n";
        }

        $meta = $this->meta;
        $meta['__hash'] = $meta['__hash_new'];

        return
            file::write(fs::ds($cache, "{$this->language}.html"), $html)
        and json::encode_file(fs::ds($cache, "{$this->language}.json"), $meta);
    }

    /**
     * Make all media (files in media directory) publicly available.
     * --
     * @param string $file Publish only this particular file (relative path!)
     * --
     * @return boolean
     */
    function publish_media($file=null)
    {
        $this_media = fs::ds($this->path, 'media', $file);

        if ($file)
        {
            if (file::exists($this_media))
            {
                $public_media = fs::pubpath($this->quid, 'media', dir::name($file));

                if (!dir::create($public_media))
                    return false;

                return file::copy($this_media, $public_media);
            }
        }
        else
        {
            if (dir::exists($this_media))
            {
                $public_media = fs::pubpath($this->quid, 'media');

                if (!dir::create($public_media))
                    return false;

                dir::copy($this_media, $public_media);
            }
        }


        return true;
    }

    /**
     * Make all media NOT publicly available anymore.
     * --
     * @param string $file Un-publish only this particular file (relative path)
     * --
     * @return boolean
     */
    function unpublish_media($file=null)
    {
        $public_media = fs::pubpath($this->quid, $file);

        if ($file)
        {
            if (file::exists($public_media))
            {
                return file::remove($public_media);
            }
        }
        else
        {
            if (dir::exists($public_media))
            {
                return dir::remove($public_media);
            }
        }

        return true;
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
        if (!array_key_exists($key, $this->meta(null)))
        {
            return $default;
        }
        else
        {
            return $this->meta(null)[$key];
        }
    }

    /**
     * Get all available languages of this post.
     * --
     * @return array [ _def, si, ru, en, ... ]
     */
    function list_languages()
    {
        $languages = [];

        foreach (fs::ls($this->path, '*.md') as $file)
        {
            $languages[] = substr($file, 0, -3); // .md
        }

        return $languages;
    }

    /**
     * Get an array list of versions.
     * --
     * @return array
     *         [ version => [ version, filename, hash ] ]
     */
    function list_versions()
    {
        $path = fs::ds($this->path, 'versions');
        $versions = [];

        if (!dir::exists($path))
        {
            return $versions;
        }

        foreach (fs::ls($path) as $filename)
        {
            if (preg_match('#^v([0-9]+)\.([a-z_]+)\.(.*?).md$#', $filename, $match))
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
     * For this version, meta will be constructed.
     * Last written version can be accessed with $id = -1, the one before with -2,
     * first version with $id = 1, etc...
     * --
     * @param integer $id version position
     * --
     * @return array
     */
    function version_meta($id=0)
    {
        $versions = $this->list_versions();

        if ($id)
        {
            $version = array_slice($versions, $id, 1);
            return array_pop($versions);
        }
        else
        {
            $version = array_pop($versions);
            $version = $version ? ++$version['version'] : 1;
            $hash = $this->get_hash(true);
            return [
                'version'  => $version,
                'filename' => "v{$version}.{$this->language}.{$hash}.md",
                'hash'     => $hash
            ];
        }
    }

    /**
     * Write a new version of this post.
     * --
     * @param boolean $force Write even if hash is the same as in previous version.
     * --
     * @return boolean
     */
    function up_version($force=false)
    {
        $current = $this->version_meta(0);

        if (!$force)
        {
            $previous = $this->version_meta(-1);
            if (!empty($previous) && $previous['hash'] === $current['hash'])
            {
                return true;
            }
        }

        $path = fs::ds($this->path, 'versions', $current['filename']);
        return !! file::write($path, $this->source());
    }

    /**
     * Downgrade current post to particular version.
     * Don't forget to reload cache after downgrade!
     * --
     * @param integer $version
     * --
     * @throws mysli\std\post\exception\post 10 Cannot switch to version, not found.
     * --
     * @return boolean
     */
    function to_version($version)
    {
        $versions = $this->list_versions();

        if (!isset($versions[(int)$version]))
        {
            throw new exception\post(
                "Cannot switch to version `{$version}`. Not found.", 10);
        }

        $version = $versions[$version];
        $filename = $version['filename'];

        $source = file::read(fs::ds($this->path, 'versions', $filename));

        $this->reset();

        return $this->write_source($source);
    }

    /**
     * Write source file.
     * --
     * @param string $source
     * --
     * @return boolean
     */
    function write_source($source)
    {
        return file::write(fs::ds($this->path, "{$this->language}.md"), $source);
    }

    /**
     * Get post's source as a string.
     * --
     * @param string $section
     *        meta|body, null for both in string version!
     * --
     * @return mixed
     */
    function source($section=null)
    {
        if ($this->source === null)
        {
            $this->load_source();
        }

        if ($section === 'meta')
        {
            return $this->source['meta'];
        }
        elseif ($section === 'body')
        {
            return $this->source['body'];
        }
        else
        {
            $meta = $this->source('meta');
            $meta = ym::encode($meta);
            return
                $meta."\n\n".
                str_repeat('=', 80)."\n\n".
                $this->source('body')."\n";
        }
    }

    /**
     * Return post's data as an array!
     * --
     * @param string $page
     *        Null, get full array of post.
     *        String, get specific array for page.
     * --
     * @return array
     */
    function as_array($page='_default')
    {
        $meta = $this->meta($page);
        $meta['language'] = $this->language;
        $meta['body'] = $this->html($page);
        return $meta;
    }

    /**
     * Load source file!
     */
    function load_source()
    {
        $source = file::read(fs::ds($this->path, "{$this->language}.md"));
        $source = preg_split('/^={3,}$/m', $source, 2);

        $this->source['meta'] = ym::decode($source[0]);
        $this->source['body'] = trim($source[1]);
    }

    /**
     * Process.
     * --
     * @return string
     */
    protected function process()
    {
        // Get source
        $source = $this->source('body');
        $meta = $this->source('meta');

        // Split source to multiple pages
        $page_sources = preg_split(
            '/^={3,}$/m', $source, -1,
            PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);

        // Set some defaults
        if (!isset($meta['references'])) $meta['references'] = [];
        if (!isset($meta['table_of_contents'])) $meta['table_of_contents'] = [];
        if (!isset($meta['pages'])) $meta['pages'] = [];

        $meta['__hash_new'] = $this->get_hash(true);
        $meta['quid'] = substr($this->quid, strpos($this->quid, '/')+1);

        $pages = [];

        foreach ($page_sources as $k => $page)
        {
            $parser = new parser($page);

            // Add costume link handling to the parser for media files.
            $link = $parser->get_processor('mysli.markdown.module.link');
            $link->set_local_url('#^/(.*?)(?<!\.html|\.php|\.htm)$#', '/'.$this->quid);

            $html = markdown::process($parser);

            // Table of Contents
            $headers = $parser->get_processor('mysli.markdown.module.header');
            $toc = $headers->as_array();

            if (count($toc))
            {
                $title = reset($toc);
                $pid = $title['fid'];
                $ptitle = $title['title'];
            }
            else
            {
                $pid = "page-{$k}";
                $ptitle = "Page: {$k}";
            }

            if (count($page_sources) === 1)
            {
                $pid = '_default';
            }

            // Footnotes
            $footnote = $parser->get_processor('mysli.markdown.module.footnote');
            $meta['references'][$pid] = $footnote->as_array();

            // Add ToC
            $meta['table_of_contents'][$pid] = $toc;

            // Previous
            if ($k > 0)
            {
                $previous = arr::last_key($meta['pages']);
                $meta['pages'][$previous]['next'] = [ $pid, $ptitle ];
            }
            else
            {
                $previous = null;
            }

            // Pages
            $meta['pages'][$pid] = [
                'title'      => $ptitle,
                'quid'       => $pid,
                'fquid'      => $meta['quid'].'/'.$pid,
                'index'      => $k+1,
                'next'       => null,
                'previous'   => $previous ? [ $previous, $meta['pages'][$previous]['title'] ] : null,
                'is_first'   => $k === 0,
                'is_last'    => $k === count($page_sources)-1,
                'is_single'  => count($page_sources) === 1,
            ];

            $pages[$pid] = $html;
        }

        $this->meta = $meta;
        $this->html = $pages;
    }
}
