<?php

namespace mysli\web\assets;

__use(__namespace__, '
    ./assets
    mysli.framework.fs/fs,file
');

class services
{
    /**
     * Get list of files to be ignored for production.
     * This is used mostly for pkgc event, to generate list of files to be
     * ignored.
     * @param  string $package
     * @param  array &$ignore
     */
    static function generate_ignore_list($package, array &$ignore)
    {
        list($as_src, $as_dest, $as_map) = assets::get_paths($package);

        $ignore[] = $as_src.'/';
        $map = false;

        try
        {
            $map = assets::get_map($package, $as_src, $as_map);
        }
        catch (\Exception $e) {
            return;
        }

        if (is_array($map) && isset($map['files']) && is_array($map['files']))
        {
            $extlist = is_array($map['settings']) && is_array($map['settings']['ext'])
                ? $map['settings']['ext']
                : [];

            foreach ($map['files'] as $file)
            {
                if (!is_array($file))
                {
                    continue;
                }

                if (isset($file['compress']) && $file['compress'] &&
                    isset($file['include']) && is_array($file['include']))
                {
                    foreach ($file['include'] as $include)
                    {
                        $include = assets::parse_extention($include, $extlist);
                        $ignore[] = fs::ds($as_dest, $include);
                    }
                }
            }
        }
    }
    /**
     * Insert map file back to phar.
     * @param  string $package
     * @param  object $phar
     */
    static function map_to_phar($package, $phar)
    {
        list($source, $dest, $map) = assets::get_paths($package);

        if (!file::exists(fs::pkgreal($package, $source, $map)))
        {
            return;
        }

        $phar->addEmptyDir($source);
        $phar->addFile(
            fs::pkgreal($package, $source, $map), fs::ds($source, $map)
        );
    }
}
