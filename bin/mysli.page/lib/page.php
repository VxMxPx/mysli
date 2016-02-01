<?php

namespace mysli\page; class page
{
    const __use = <<<fin
    mysli.markdown.{ markdown, parser }
    mysli.toolkit.{ ym, json }
    mysli.toolkit.fs.{ fs, dir, file }
fin;

    protected $id;
    protected $path;

    protected $source = null;

    protected $html = null;
    protected $meta = null;

    /**
     * Load a page.
     * --
     * @param string $id
     * @param array  $data
     */
    function __construct($id)
    {
        $this->id = $id;
        $this->path = fs::cntpath('pages', $id);

        // Make dirs if not there
        dir::create(fs::ds($this->path, 'cache~'));
        dir::create(fs::ds($this->path, 'versions'));
    }

    /**
     * Get particular key from meta.
     * --
     * @param string $key
     * @param mixed  $default Return if key not found.
     * --
     * @return mixed
     */
    function get($key, $default=null)
    {
        $meta = $this->get_meta();

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
     * Get source version of the page.
     * --
     * @return string
     */
    function get_source()
    {
        if ($this->source === null)
        {
            $this->source = file::read(fs::ds($this->path, 'page.md'));
        }

        return $this->source;
    }

    /**
     * Get HTML version of the page.
     * --
     * @param boolean $fresh
     *        Grab fresh html from `post.md` rather than reading cached version.
     * --
     * @return string
     */
    function get_html($fresh=false)
    {
        if ($this->html === null || $fresh)
        {
            if (file::exists(fs::ds($this->path, 'cache~/page.html')) && !$fresh)
            {
                $this->html = file::read(fs::ds($this->path, 'cache~/page.html'));
            }
            else
            {
                $source = $this->get_source();
                $source = preg_split('/^\={3,}$/m', $source, 2);
                $page = trim($source[1]);

                $this->html = $this->process_markdown($page);
            }
        }

        return $this->html;
    }

    /**
     * Get meta data for page.
     * --
     * @param boolean $fresh
     *        Grab fresh meta from `post.md` rather than reading cached version.
     * --
     * @return array
     */
    function get_meta($fresh=false)
    {
        if ($this->meta === null || $fresh)
        {
            if (file::exists(fs::ds($this->path, 'cache~/meta.json')) && !$fresh)
            {
                $this->meta = json::decode_file(
                    fs::ds($this->path, 'cache~/meta.json'), true);
            }
            else
            {
                $source = $this->get_source();
                $source = preg_split('/^\={3,}$/m', $source, 2);
                $meta = trim($source[0]);

                $this->meta = ym::decode($meta);
            }
        }

        return $this->meta;
    }

    /**
     * Return meta + page as an array.
     * --
     * @return array
     */
    function as_array()
    {
        $meta = $this->get_meta();
        $meta['body'] = $this->get_html();
        return $meta;
    }

    /**
     * Make all media (files in media directory) publicly available.
     */
    function publish_media()
    {
        $this_media = fs::ds($this->path, 'media');

        if (dir::exists($this_media))
        {
            $public_media = fs::pubpath('pages', $this->id, 'media');
            dir::create($public_media);
            dir::copy($this_media, $public_media);
        }
    }

    /**
     * Make all media not publicly available anymore.
     */
    function unpublish_media()
    {
        $public_media = fs::pubpath('pages', $this->id);

        if (dir::exists($public_media))
        {
            dir::remove($public_media);
        }
    }

    /**
     * Check weather cached version should be refreshed.
     * --
     * @return boolean
     */
    function is_latest_cache()
    {
        if (!dir::exists(fs::ds($this->path, 'cache~'))) return false;
        if (!file::exists(fs::ds($this->path, 'cache~/hash'))) return false;

        $last_hash = trim(file::read(fs::ds($this->path, 'cache~/hash')));
        $this_hash = md5_file(fs::ds($this->path, 'page.md'));

        if ($last_hash !== $this_hash) return false;

        return true;
    }

    /**
     * Refresh cached version of file.
     * --
     * @return boolean
     */
    function refresh_cache()
    {
        $hash = md5_file(fs::ds($this->path, 'page.md'));

        {
            return

            // Write hash
            file::write(fs::ds($this->path, 'cache~/hash'), $hash)

            and

            // Write HTML
            file::write(fs::ds($this->path, 'cache~/page.html'), $this->get_html(true))

            and

            // Write Meta
            json::encode_file(
                fs::ds($this->path, 'cache~/meta.json'),
                $this->get_meta(true))

            // Done Returning
            ;
        }
    }

    /**
     * Write new version of post.
     * --
     * @return integer
     *         Written version.
     */
    function write_version()
    {
        $versions = $this->get_versions();
        $latest = array_pop($versions);
        $latest = explode('.', $latest, 2);
        $latest = ((int) $latest[0]) + 1;

        return file::write(
            fs::ds($this->path, 'versions', $latest.'.md'),
            $this->get_source()
        );
    }

    /**
     * Get all available versions.
     * --
     * @return array
     */
    function get_versions()
    {
        $path = fs::ds($this->path, 'versions');
        $versions = fs::ls($path, '*.md');
        sort($versions);

        return $versions;
    }

    /**
     * Process markdown.
     * --
     * @param string $page
     * --
     * @return string
     */
    protected function process_markdown($page)
    {
        $parser = new parser($page);
        $link = $parser->get_processor('mysli.markdown.module.link');
        $link->set_local_url('/pages/'.$this->id);

        return markdown::process($parser);
    }
}
