<?php

/**
 * Manage file list state. Write, load and observe state (changes).
 */
namespace mysli\content; class state
{
    const __use = <<<fin
        .{ media, cache, version }
        mysli.toolkit.fs.{ fs, file, dir, observer }
fin;

    /**
     * Observe content for changes.
     * --
     * @param string $cid
     *        Unique content ID to be observed (e.g blog)
     *
     * @param callable $call
     *        function ($iid, $action, $options, $stat, $file, $is_media) {}
     *        iid = Unique item identifier,
     *        action = action (as send by observer)
     *        options = as send by observer
     *        stat = statistic array (count, position)
     *        file = specific filename
     *        is_media = weather this file is located in media directory
     *
     * @param boolean  $watch
     * --
     * @return mixed
     */
    static function observe($cid, $call, $watch)
    {
        // Root of pages
        $root = fs::cntpath($cid);

        // Setup observer
        $observer = new observer($root);
        $observer->set_interval(2);
        $observer->set_ignore(['/'.cache::dir.'/', version::dir.'/']);

        // Signature file & load list
        $sigfile = fs::cntpath($cid, cache::dir, '_state.json');

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

        // Observe files
        return $observer->observe(
        function ($fpath, $action, $options, $stat) use ($root, $call)
        {
            $rpath = substr($fpath, strlen($root)+1);
            $media = strpos($rpath, fs::ds.media::dir.fs::ds);

            if ($media !== false)
            {
                $iid = substr($rpath, 0, $media);
                $file = substr($rpath, $media+8);
            }
            else
            {
                $iid = dir::name($rpath);
                $file = file::name($rpath);
            }

            // Call costume method
            return $call($iid, $action, $options, $stat, $file, !!$media);

        }, true);
    }
}
