<?php

namespace mysli\js\external\tplp;

__use(__namespace__, '
    ./external -> js\external
');

class util
{
    static function tags($package, $type=null)
    {
        $links = self::resolve_package_or_library($package, $type);
        $output = '';
        
        foreach ($links as $link)
        {
            if ($type === 'css')
                $output .= "\n<link rel=\"stylesheet\" href=\"{$link}\" />";
            else
                $output .= "\n<script src=\"{$link}\"></script>";
        }
        
        return $output;
    }

    static function links($package, $type=null)
    {
        return implode("\n", self::resolve_package_or_library($package, $type));
    }


    private static function resolve_package_or_library($package, $type=null)
    {
        if (strpos($package, '.') === false || substr($package, 0, 1) === '\\')
        {
            // Need only links for one library
            if (substr($package, 0, 1) === '\\')
                $package = substr($package, 1);

            return js\external::get_lib_links($package, $type);
        }
        else
        {
            // Need links for a package
            return js\external::get_pkg_links($package, $type);
        }
    }
}
