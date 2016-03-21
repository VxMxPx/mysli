<?php

namespace mysli\content; class language
{
    const __use = <<<fin
        mysli.toolkit.fs.{ fs }
fin;

    /**
     * Get all available languages on an item.
     * --
     * @return array [ _def, si, ru, en, ... ]
     */
    function ls($cid, $iid)
    {
        $path = fs::cntpath($cid, $iid);
        $languages = [];

        foreach (fs::ls($path, '*.post') as $file)
            $languages[] = substr($file, 0, -5); // .post

        return $languages;
    }
}
