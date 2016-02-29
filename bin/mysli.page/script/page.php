<?php

namespace mysli\page\root\script; class page
{
    const __use = '
        .{ page -> lib.page }
        mysli.std.post
        mysli.toolkit.{ json }
        mysli.toolkit.fs.{ fs, file, dir, observer }
        mysli.toolkit.cli.{ prog, ui, input }
    ';

    /**
     * Run Blog CLI.
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
            'help' => 'Process all pages without cache and create list(s).'
        ])
        ->create_parameter('--clean/-c', [
            'type' => 'boolean',
            'def'  => false,
            'help' => 'Clear all cache and un-publish media.'
        ])
        ->create_parameter('--watch/-w', [
            'type' => 'boolean',
            'def'  => false,
            'help' => 'Watch directory and re-process when changes occurs.'
        ]);

        if (null !== ($r = prog::validate_and_print($prog, $args)))
            return $r;

        list($build, $clean, $watch) = $prog->get_values('-b', '-c', '-w');

        if ($clean)
            static::clean();

        if ($build)
        {
            static::build(false);
        }

        if ($watch)
        {
            return static::build(true);
        }
        else
        {
            return true;
        }
    }

    /*
    --- Protected --------------------------------------------------------------
     */

    /**
     * Clean page's cache!
     * --
     * @param string $path
     */
    protected static function clean($path=null)
    {
        $root = fs::cntpath('pages', $path);


        if (!$path && dir::exists(fs::ds($root, 'cache~')))
        {
            dir::remove(fs::ds($root, 'cache~'))
                ? ui::success('Main cache~ removed')
                : ui::error('Failed to remove main cache~');
        }

        foreach (fs::ls($root) as $page)
        {
            if (!dir::exists(fs::ds($root, $page))
            || substr($page, -1) === '~'
            || substr($page, 0, 1) === '.'
            || $page === '@versions' || $page === '@media')
            {
                continue;
            }

            $paged = fs::ds($root, $page);
            ui::title("Page ".fs::ds($path, $page));

            if (!lib\page::has(fs::ds($path, $page)))
            {
                ui::info('No post found in this directory.', null, 1);
            }
            else
            {
                if (dir::exists(fs::ds($paged, '@media')))
                {
                    $post = new post(fs::ds('pages', $path, $page));
                    $post->unpublish_media()
                        ? ui::success('Unpublished', null, 1)
                        : ui::error('Unpublish Failed', null, 1);
                }
                if (dir::exists(fs::ds($paged, 'cache~')))
                {
                    dir::remove(fs::ds($paged, 'cache~'))
                        ? ui::success('Removed cache~', null, 1)
                        : ui::error('Failed to remove cache~', null, 1);
                }
            }

            ui::info('All Done', null, 1);
            static::clean(fs::ds($path, $page));
        }

        return true;
    }

    /**
     * Parse templates in particular path, and watch for change.
     * --
     * @return boolean
    */
    protected static function build($watch)
    {
        // Root of pages
        $root = fs::cntpath('pages');

        // Setup observer
        $observer = new observer($root);
        $observer->set_interval(2);
        $observer->set_ignore(['cache~/', '@versions/']);

        $sigfile = fs::cntpath('pages/cache~/observer.json');
        if (file::exists($sigfile))
        {
            $signatures = json::decode_file($sigfile, true);
            $observer->set_signatures($signatures);
        }

        $observer->set_write_signatures($sigfile);

        if (!$watch)
            $observer->set_limit(1);

        $last_quid = null;
        $page = null;

        // Observe files
        return $observer->observe(
            function ($fpath, $action, $options) use ($root, &$last_quid, &$page)
            {
                $rpath = substr($fpath, strlen($root)+1);
                $media = strpos($rpath, fs::ds.'@media'.fs::ds);

                if ($media !== false)
                {
                    $quid = substr($rpath, 0, $media);
                    $file = substr($rpath, $media+8);
                }
                else
                {
                    $quid = dir::name($rpath);
                    $file = file::name($rpath);
                }

                if ($last_quid !== $quid && lib\page::has($quid))
                {
                    ui::title("{$quid}");
                    $page = new post("pages/{$quid}");
                    $last_quid = $quid;
                }

                ui::info(ucfirst($action), $file, 1);

                if ($media !== false)
                {
                    // `added|removed|modified|renamed|moved`
                    if (in_array($action, ['modified', 'added']) ||
                        (in_array($action, ['renamed', 'moved'])
                            && isset($options['from'])))
                    {
                        $page->publish_media($file)
                            ? ui::success('Published', null, 2)
                            : ui::error('Publish Failed', null, 2);
                    }
                    elseif (in_array($action, ['removed']) ||
                            (in_array($action, ['renamed', 'moved'])
                                && isset($options['to'])))
                    {
                        $page->unpublish_media($file)
                            ? ui::success('Unpublished', null, 2)
                            : ui::error('Unpublish Failed', null, 2);
                    }

                    return;
                }

                // Cache page!
                if (substr($file, -3) === '.md')
                {
                    if ($action !== 'removed' && !isset($options['to']))
                    {
                        $page->switch_language(substr($file, 0, -3));
                        $page->make_cache()
                            ? ui::success('Cache', null, 2)
                            : ui::error('Cache Failed', null, 2);
                        $page->up_version()
                            ? ui::success('Version', null, 2)
                            : ui::error('Version Failed', null, 2);
                        // Changes in page, refresh
                    }
                }
                else
                {
                    ui::info('Nothing to do...', null, 1);
                }
            }, true);
    }
}
