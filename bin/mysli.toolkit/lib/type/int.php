<?php

namespace mysli\toolkit\type; class int
{
    /**
     * Random, using mt_rand or openssl if available, and if $better is true
     * http://php.net/manual/en/function.openssl-random-pseudo-bytes.php#104322
     * --
     * @param integer $min
     * @param integer $max
     * @param boolean $better
     * --
     * @return integer
     */
    static function random($min, $max, $better=true)
    {
        if (!function_exists('openssl_random_pseudo_bytes') || !$better)
        {
            return mt_rand($min, $max);
        }

        $range = $max - $min;

        if ($range == 0)
        {
            return $min;
        }

        $log = log($range, 2);
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1

        do
        {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes, $s)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd >= $range);

        return $min + $rnd;
    }
}
