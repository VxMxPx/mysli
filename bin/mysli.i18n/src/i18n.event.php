<?php

namespace mysli\i18n; class i18n_event
{
    const __use = '.i18n';
    
    /**
     * Get list of files to be ignored for production.
     * @param  string $package
     * @param  array &$ignore
     */
    static function generate_ignore_list($package, array &$ignore)
    {
        list($source, $destination) = i18n::get_paths($package, false);

        if ($source !== $destination)
        {
            $ignore[] = rtrim($source, '\\/').'/';
        }
    }
}
