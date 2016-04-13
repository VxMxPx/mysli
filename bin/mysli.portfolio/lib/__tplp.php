<?php

namespace mysli\portfolio; class __tplp
{
    const __use = <<<fin
        .{ portfolio }
        mysli.frontend.{ __tplp -> frontend.tplp }
        mysli.toolkit.{ config, route }
fin;

    /**
     * Return internal blog URL.
     * --
     * @param string  $iid
     * @param string  $type     URI type: clip|small|full
     * @param boolean $absolute Return full absolute URL (inc. domain)
     * --
     * @return string
     */
    static function url($iid='', $type='full', $absolute=false)
    {
        $url = portfolio::cid."/{$iid}/{$type}.jpg";
        return frontend\tplp::url($url, $absolute);
    }
}
