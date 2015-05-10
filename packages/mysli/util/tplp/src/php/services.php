<?php

namespace mysli\util\tplp;

__use(__namespace__, '
    ./tplp
');

class services
{
    /**
     * Get list of files to be ignored for production.
     * @param  string $package
     * @param  array &$ignore
     */
    static function generate_ignore_list($package, array &$ignore)
    {
        list($source, $destination) = tplp::get_paths($package, false);

        if ($source !== $destination)
        {
            $ignore[] = rtrim($source, '\\/').'/';
        }
    }
}
