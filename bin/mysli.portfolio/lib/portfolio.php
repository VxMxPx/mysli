<?php

namespace mysli\portfolio; class portfolio
{
    const __use = <<<fin
        mysli.toolkit.{ ym }
        mysli.toolkit.fs.{ fs, file, dir }
fin;

    const cid = 'portfolio';

    /**
     * Get all portfolio items.
     * --
     * @return array
     */
    static function all()
    {
        $portfolio = [];
        $files = file::find(fs::cntpath(static::cid), 'about.ym');

        foreach ($files as $path)
        {
            $meta = ym::decode_file($path);

            // set iid e.g. 2016/art-work
            $iid = substr(dir::name($path), strlen(fs::cntpath(static::cid)));
            $iid = trim($iid, '/\\');

            // published
            $meta['published'] = isset($meta['published'])
                ? (bool) $meta['published']
                : true;

            $meta['iid'] = $iid;
            $portfolio[] = $meta;
        }

        return $portfolio;
    }
}
