<?php

namespace mysli\js\external;

__use(__namespace__, '
    ./__init
    mysli.framework.pkgm
    mysli.framework.exception/* -> framework\exception\*
    mysli.framework.fs/dir,fs,file
    mysli.framework.type/arr
    mysli.web.assets
    mysli.util.curl
    mysli.util.config
');

class external
{
    /**
     * Select package (get all links of particular type, if no type, all links)
     * @param  string $package
     * @param  string $type
     * @return array
     */
    static function get_pkg_links($package, $type=null)
    {
        // Package's meta
        $meta = pkgm::meta($package);
        $defaults = __init::defaults();
        $libraries = [];

        if (isset($meta['js.external']))
            $defaults = arr::merge($defaults, $meta['js.external']);


        if (!isset($defaults['require']))
            return [];

        foreach ($defaults['require'] as $library) 
        {
            $libraries = array_merge(
                $libraries, self::get_lib_links($library, $type, $defaults)
            );
        }

        return $libraries;
    }

    /**
     * Get links for particular JavaScript library.
     * @param  string $library
     * @param  string $type
     * @param  array  $defaults
     * @return array
     */
    static function get_lib_links($library, $type=null, array $defaults=null)
    {
        if (!$defaults)
            $defaults = __init::defaults();
        
        $c = config::select(__namespace__);
        $links = [];

        // Do we have a library?
        if (!isset($defaults['libraries'][$library]))
            throw new framework\exception\not_found(
                "Tying to use undefined library: `{$library}`.", 10
            );
        else
            $library_meta = $defaults['libraries'][$library];

        // Do we have dependencies?
        if (isset($library_meta['require']) && is_array($library_meta['require']))
        {
            foreach ($library_meta['require'] as $require) 
            {
                $links = array_merge($links, self::get_lib_links($require, $type));
            }
        }

        // Do we need local?
        $local = $c->get('local', []);
        $dev = $c->get('development', []);

        $is_local = isset($local[$library]);
        $is_dev = isset($dev[$library]);
        $version = $library_meta['version'];

        if ($is_dev && isset($library_meta['dev']) && $library_meta['dev'])
            $urls = $library_meta['dev'];
        else
            $urls = $library_meta['min'];

        if ($is_local)
        {
            if (!self::has_local($library, $version, $urls))
                self::fetch_library($library, $version, $urls);
                
            $links = array_merge($links, self::get_local($library, $version, $urls));
        }
        else
        {
            $links = array_merge($links, self::get_remote($library, $version, $urls));
        }

        if ($type)
        {
            foreach ($links as $id => &$link) {
                if (substr($link, -(strlen($type))) !== $type)
                    unset($links[$id]);
            }
            unset($link);
        }

        return $links;
    }

    /**
     * Check if local files exists for a library.
     * @param  string  $library
     * @param  string  $version
     * @param  array   $urls
     * @return boolean
     */
    private static function has_local($library, $version, array $urls)
    {
        $public_dir = fs::ds(
            assets::get_public_url('mysli.js.external'),
            "{$library}-{$version}"
        );

        return dir::exists($public_dir);
    }

    /**
     * Fetch library files.
     * @param  string  $library
     * @param  string  $version
     * @param  array   $urls
     * @return integer number of downloaded files
     */
    private static function fetch_library($library, $version, array $urls)
    {
        $downloaded = 0;

        if (!\core\pkg::is_enabled('mysli.util.curl'))
            throw new framework\exception\not_found(
                'You need to enable `mysli.util.curl` '.
                "to fetch `{$library}` and save it locally."
            );

        $public_dir = fs::ds(
            assets::get_public_url('mysli.js.external'),
            "{$library}-{$version}"
        );

        if (!dir::exists($public_dir))
            dir::create($public_dir);

        foreach ($urls as $url) 
        {
            $url = str_replace('{version}', $version, $url);
        
            if (substr($url, 0, 2) === '//')
                $url = "http:{$url}";

            $fetched = curl::get($url);

            if ($fetched)
                $downloaded =+ file::write(
                    fs::ds($public_dir, file::name($url, true)),
                    $fetched
                );
        }

        return $downloaded;
    }

    /**
     * Get local library links.
     * @param  string  $library
     * @param  string  $version
     * @param  array   $urls
     * @return array
     */
    private static function get_local($library, $version, array $urls)
    {
        $links = [];
        $public_url =
            assets::get_public_url('mysli.js.external')
            ."/{$library}-{$version}";

        foreach ($urls as $url) 
        {
            $links[] = $public_url."/".file::name(
                str_replace('{version}', $version, $url), true
            );
        }

        return $links;
    }

    /**
     * Get remote library links.
     * @param  string  $library
     * @param  string  $version
     * @param  array   $urls
     * @return array
     */
    private static function get_remote($library, $version, array $urls)
    {
        $links = [];

        foreach ($urls as $url) 
        {
            $links[] = str_replace('{version}', $version, $url);
        }

        return $links;
    }
}
