<?php

namespace mysli\page\root\script; class page
{
    const __use = '
        .{ page -> lib.page }
        mysli.content.{ collection, state, cache, media, version }
        mysli.toolkit.{ json }
        mysli.toolkit.fs.{ fs, file, dir }
        mysli.toolkit.cli.{ prog, ui, input }
    ';

    /**
     * Run Page CLI.
     * --
     * @param array $args
     * --
     * @return boolean
     */
    static function __run(array $args)
    {
        /*
        Set params.
         */
        $prog = new prog('Mysli Page', __CLASS__);

        $prog->set_help(true);
        $prog->set_version('mysli.page', true);

        $prog
        ->create_parameter('--build/-b', [
            'type' => 'boolean',
            'def'  => false,
            'help' => 'Process and cache all pages and create main list.'
        ])
        ->create_parameter('--clean/-c', [
            'type' => 'boolean',
            'def'  => false,
            'help' => 'Remove all cache and un-publish media.'
        ])
        ->create_parameter('--clean-versions', [
            'type' => 'boolean',
            'def'  => false,
            'help' => 'Remove versions for all posts.'
        ])
        ->create_parameter('--watch/-w', [
            'type' => 'boolean',
            'def'  => false,
            'help' => 'Watch directory and process individual page(s) when changes occurs.'
        ]);

        if (null !== ($r = prog::validate_and_print($prog, $args)))
            return $r;

        list($build, $clean, $clean_versions, $watch) =
            $prog->get_values('-b', '-c', '--clean-versions', '-w');

        if ($clean)
        {
            static::clean();
        }

        if ($clean_versions)
        {
            static::clean_versions();
        }

        if ($build || $watch)
        {
            return static::build($watch);
        }

        return true;
    }

    /*
    --- Protected --------------------------------------------------------------
     */

    /**
     * Clean pages' cache.
     */
    protected static function clean()
    {
        ui::nl(); ui::info('Cleaning...');

        $cache_dir = fs::cntpath(lib\page::cid, cache::dir);

        if (dir::exists($cache_dir))
            dir::remove($cache_dir)
                ? ui::success('Cache cleaned.')
                : ui::error('Failed to clean cache.');
        else ui::info('No cache found.');

        ui::nl();
    }

    /**
     * Clean post's versions.
     */
    protected static function clean_versions()
    {
        collection::filter(lib\page::cid, function ($iid, $_, $stat)
        {
            ui::progress($stat['position'], $stat['count'], 'Versions');
            $path = fs::cntpath(lib\page::cid, $iid, version::dir);
            if (dir::exists($path))
            {
                if (!dir::remove($path))
                    ui::error('Failed to remove version', null);
            }
        }, '_def');

        ui::nl();
    }

    /**
     * Parse templates in particular path, and watch for change.
     * --
     * @return boolean
    */
    protected static function build($watch)
    {
        if ($watch)
        {
            ui::nl();
            ui::info('Watching');
        }

        $cache_root = fs::cntpath(lib\page::cid, cache::dir);
        !dir::exists($cache_root) and dir::create($cache_root);

        state::observe(
        lib\page::cid,
        function ($iid, $action, array $options, array $stat, $file, $is_media) use ($watch)
        {
            if (!$watch)
            {
                ui::progress($stat['position'], $stat['count'], 'Building');
            }
            else
            {
                ui::info(ucfirst($action), $iid.': '.$file);
            }

            // It seems we're dealing with a temporary file
            if (substr($file, 0, 1) === '.' || substr($file, -1) === '~')
                return;

            // MEDIA
            if ($is_media)
            {
                $media = new media(lib\page::cid, $iid);

                if (in_array($action, ['modified', 'added']) ||
                    (in_array($action, ['renamed', 'moved'])
                        && isset($options['from'])))
                {
                    if (!$media->publish($file))
                        ui::error('Publish Failed', null, 1);
                }
                elseif (in_array($action, ['removed']) ||
                        (in_array($action, ['renamed', 'moved'])
                            && isset($options['to'])))
                {
                    if (!$media->unpublish($file))
                        ui::error('Unpublish Failed', null, 2);
                }

                return;
            }

            // POST
            if (substr($file, -5) === '.post')
            {
                if ($action !== 'removed' && !isset($options['to']))
                {
                    $language = substr($file, 0, -5);

                    if (lib\page::exists($iid, $language))
                    {
                        $cache = new cache(lib\page::cid, $iid, $language);

                        if (!$cache->is_fresh())
                        {
                            // Fully load post ... :]
                            $post = lib\page::get($iid, $language);
                            $sources = $post['.sources'];
                            unset($post['.sources']);

                            // Write post's cache
                            $cache->write($post);

                            // Write new post version
                            $version = new version(lib\page::cid, $iid, $language);
                            $version->up($sources);
                        }
                    }
                }
            }

            $watch and static::create_list();

        }, $watch);

        static::create_list();
    }

    protected static function create_list()
    {
        ui::nl(2);
        ui::info('Creating list');

        $cache_filename = fs::cntpath(lib\page::cid, cache::dir, '_list_archive.json');
        $list = lib\page::all();

        // Sort by date!
        uasort($list, function ($a, $b)
        {
            $a = strtotime($a['date']);
            $b = strtotime($b['date']);
            if ($a === $b) return 0;
            return ($a > $b) ? -1 : 1;
        });

        json::encode_file($cache_filename, $list)
            ? ui::success('List was written', null)
            : ui::error('Failed to write list', null);
    }
}
