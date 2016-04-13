<?php

namespace mysli\portfolio\root\script; class portfolio
{
    const __use = '
        .{ portfolio -> lib.portfolio }
        mysli.toolkit.{ json }
        mysli.toolkit.fs.{ fs, file, dir, observer }
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
        $prog = new prog('Mysli Portfolio', __CLASS__);

        $prog->set_help(true);
        $prog->set_version('mysli.portfolio', true);

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
        ->create_parameter('--watch/-w', [
            'type' => 'boolean',
            'def'  => false,
            'help' => 'Watch directory and process individual page(s) when changes occurs.'
        ]);

        if (null !== ($r = prog::validate_and_print($prog, $args)))
            return $r;

        list($build, $clean, $watch) =
            $prog->get_values('-b', '-c', '-w');

        if ($clean)
        {
            static::clean();
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

        $cache_dir  = fs::cntpath(lib\portfolio::cid, '.cache');
        $public_dir = fs::pubpath(lib\portfolio::cid);

        // clear cache directory
        if (dir::exists($cache_dir))
            dir::remove($cache_dir)
                ? ui::success('Cache cleaned.')
                : ui::error('Failed to clean cache.');
        else ui::info('No cache found.');

        // clear public directory
        if (dir::exists($public_dir))
            dir::remove($public_dir)
                ? ui::success('Published media cleaned.')
                : ui::error('Failed to clean published media.');
        else ui::info('No published media found.');

        ui::nl();
    }

    /**
     * Parse portfolio and/or watch for change.
     * --
     * @return boolean
    */
    protected static function build($watch)
    {
        // print watch title
        if ($watch)
        {
            ui::nl();
            ui::info('Watching');
        }

        // set content id
        $cid = lib\portfolio::cid;

        // cache directory
        $cache_root = fs::cntpath($cid, '.cache');
        !dir::exists($cache_root) and dir::create($cache_root);

        // root of pages
        $root = fs::cntpath($cid);

        // setup observer
        $observer = new observer($root);
        $observer->set_interval(2);
        $observer->set_ignore(['/.cache/']);

        // signature file and load list
        $sigfile = fs::cntpath($cid, '.cache', '_state.json');

        if (file::exists($sigfile))
        {
            $signatures = json::decode_file($sigfile, true);
            $observer->set_signatures($signatures);
        }

        $observer->set_write_signatures($sigfile);

        if (!$watch)
        {
            $observer->set_limit(1);
        }

        // set full absolute public path
        $pubpath = fs::pubpath($cid);

        // public path there?
        dir::exists($pubpath) or dir::create($pubpath);

        // start observing
        $observer->observe(
        function ($fpath, $action, $options, $stat) use ($root, $pubpath, $watch)
        {
            // see if the modification is regarding one of the image files
            if (!in_array(file::name($fpath, false), ['clip', 'small', 'full'])) return;

            // extract iid, e.g. 2015/image-page
            $iid = substr($fpath, strlen($root));
            $iid = dir::name($iid);
            $iid = trim($iid, '/\\');

            // extract filename, e.g. full.jpg
            $filename = file::name($fpath);

            if ($action === 'removed')
            {
                $pubfile = fs::ds($pubpath, $iid, $filename);

                file::exists($pubfile)
                and file::remove($pubfile)
                and ui::info("Removed {$iid}/{$filename}");

                return;
            }
            if ($action === 'modified' || $action === 'added')
            {
                $iid_pubpath = fs::ds($pubpath, $iid);
                dir::exists($iid_pubpath) or dir::create($iid_pubpath);

                file::copy($fpath, $iid_pubpath)
                    ? ui::success("Published: {$iid}/{$filename}")
                    : ui::error("Failed to publish: {$iid}/{$filename}");

                return;
            }

            $watch and static::create_list();

        }, true);

        static::create_list();
    }

    protected static function create_list()
    {
        ui::nl(2);
        ui::info('Creating list');

        $cache_filename = fs::cntpath(lib\portfolio::cid, '.cache', '_list_all.json');
        $list = lib\portfolio::all();

        // sort by date
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
