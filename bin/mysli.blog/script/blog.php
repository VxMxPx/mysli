<?php

namespace mysli\blog\root\script; class blog
{
    const __use = '
        .{ blog -> lib.blog }
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
        $prog = new prog('Mysli Blog Tplp', __CLASS__);

        $prog->set_help(true);
        $prog->set_version('mysli.blog', true);

        $prog
        ->create_parameter('--build/-b', [
            'type' => 'boolean',
            'def'  => false,
            'help' => 'Process all posts without cache and create list(s).'
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
     * Clean posts' cache!
     */
    protected static function clean()
    {
        $root = fs::cntpath('blog');
        ui::info('Starting clean process...');
        ui::nl();

        if (dir::exists(fs::ds($root, 'cache~')))
        {
            dir::remove(fs::ds($root, 'cache~'))
                ? ui::success('    OK', 'Remove main cache~')
                : ui::error('    FAILED', 'Remove main cache~');
        }

        ui::nl();

        foreach (fs::ls($root) as $year)
        {
            if (!dir::exists(fs::ds($root, $year))) continue;
            if (substr($year, -1) === '~') continue;
            if (substr($year, 0, 1) === '.') continue;

            foreach (fs::ls(fs::ds($root, $year)) as $slug)
            {
                $postd = fs::ds($root, $year, $slug);
                ui::info("Post", "{$year}/{$slug}");

                if (dir::exists(fs::ds($postd, 'media')))
                {
                    $post = new post("blog/{$year}/{$slug}");
                    $post->unpublish_media()
                        ? ui::success('    OK', 'Un-publish media')
                        : ui::error('    FAILED', 'Un-publish media');
                }

                if (dir::exists(fs::ds($postd, 'cache~')))
                {
                    dir::remove(fs::ds($postd, 'cache~'))
                        ? ui::success('    OK', 'Remove cache~')
                        : ui::error('    FAILED', 'Remove cache~');
                }
            }
        }

        ui::nl();
        ui::info('All done!');
    }

    /**
     * Parse templates in particular path, and watch for change.
     * --
     * @return boolean
    */
    protected static function build($watch)
    {
        // Root of blog
        $root = fs::cntpath('blog');

        // Create list
        lib\blog::refresh_list();

        // Setup observer
        $observer = new observer($root);
        $observer->set_interval(2);
        $observer->set_ignore(['cache~/', 'versions/']);

        $sigfile = fs::cntpath('blog/cache~/observer.json');
        if (file::exists($sigfile))
        {
            $signatures = json::decode_file($sigfile, true);
            $observer->set_signatures($signatures);
        }

        $observer->set_write_signatures($sigfile);

        if (!$watch)
            $observer->set_limit(1);

        $last_quid = null;
        $post = null;

        // Observe files
        return $observer->observe(
            function ($fpath, $action, $options) use ($root, &$last_quid, &$post)
            {
                $rpath = substr($fpath, strlen($root)+1);
                $segments = explode(fs::ds, $rpath);

                if (count($segments) < 2) return;

                $year = $segments[0];
                $slug = $segments[1];
                $quid = "{$year}/{$slug}";

                if ($last_quid !== $quid)
                {
                    ui::info("{$quid}");
                    $post = new post("blog/{$quid}");
                    $last_quid = $quid;
                }

                $rdir = isset($segments[2]) ? $segments[2] : null;

                $rrpath = array_slice($segments, 2);
                $rrpath = implode(fs::ds, $rrpath);

                ui::info("  ".ucfirst($action), $rrpath);

                if ($rdir === 'media')
                {
                    // `added|removed|modified|renamed|moved`
                    if (in_array($action, ['modified', 'added']) ||
                        (in_array($action, ['renamed', 'moved'])
                            && isset($options['from'])))
                    {
                        $post->publish_media(substr($rrpath, 6))
                            ? ui::success('    PUBLISHED')
                            : ui::error('    PUBLISH FAILED');
                    }
                    elseif (in_array($action, ['removed']) ||
                            (in_array($action, ['renamed', 'moved'])
                                && isset($options['to'])))
                    {
                        $post->unpublish_media(substr($rrpath, 6))
                            ? ui::success('    UNPUBLISHED')
                            : ui::error('    UNPUBLISH FAILED');
                    }

                    return;
                }

                // Cache post!
                if (count($segments) === 3 && substr($rrpath, -3) === '.md')
                {
                    if ($action !== 'removed' && !isset($options['to']))
                    {
                        $post->switch_language(substr($rrpath, 0, -3));
                        $post->make_cache()
                            ? ui::success('    CACHE')
                            : ui::error('    CACHE');
                        $post->up_version()
                            ? ui::success('    VERSION')
                            : ui::error('    VERSION');

                        // Changes in post, refresh
                        lib\blog::refresh_list();
                    }
                }
                else
                {
                    ui::info('    Nothing to do...');
                }
            }, true);
    }
}
