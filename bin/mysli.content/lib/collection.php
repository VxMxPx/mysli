<?php

namespace mysli\content; class collection
{
    const __use = <<<fin
        mysli.toolkit.fs.{ fs, file, dir }
fin;

    /**
     * Find all post's call function of each of them.
     * --
     * @param string   $cid
     * @param callable $call
     *        function ($iid, $language, $stat) {}
     * --
     * @return array
     */
    static function filter($cid, $call, $language='*')
    {
        $files = file::find(fs::cntpath($cid), $language.'.post');
        $collection = [];

        $stat['count'] = count($files);
        $stat['position'] = 0;

        foreach ($files as $file => $path)
        {
            if (substr($file, 0, 1) === '.') continue;

            // Extract language section
            $this_language = file::name($file);
            $this_language = substr($this_language, 0, -5);

            if ($language !== '*' && $this_language !== $language) continue;

            $iid = dir::name($file);
            $dir = file::name($iid);

            if (!preg_match('/^[a-z0-9]/', $dir)) continue;

            $stat['position']++;
            $r = $call($iid, $this_language, $stat);

            if ($r !== false) $collection[$iid] = $r;
        }

        return $collection;
    }
}
