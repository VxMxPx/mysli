<?php

namespace mysli\portfolio; class frontend
{
    const __use = <<<fin
    .{ portfolio }
    mysli.frontend.{ frontend -> fe }
    mysli.i18n
    mysli.toolkit.{ json }
    mysli.toolkit.fs.{ fs, file, dir }
    mysli.toolkit.type.{ arr }
fin;

    static function archive()
    {
        $cache_filename = fs::cntpath(portfolio::cid, '.cache', '_list_all.json');

        // Cache
        if (file::exists($cache_filename))
        {
            $list = json::decode_file($cache_filename, true);
        }
        else
        {
            $list = portfolio::all();

            //  sort by date
            uasort($list, function ($a, $b) {
                $a = strtotime($a['date']);
                $b = strtotime($b['date']);
                if ($a === $b) return 0;
                return ($a > $b) ? -1 : 1;
            });

            json::encode_file($cache_filename, $list);
        }

        fe::render(['portfolio-archive', ['mysli.portfolio', 'archive']], [
            'front' => [
                'subtitle' => i18n::select(['mysli.portfolio', 'en', null], 'ARCHIVE'),
                'type'     => 'portfolio-archive'
            ],
            'portfolio' => $list
        ]);

        return true;
    }
}
