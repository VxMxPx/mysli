<?php

namespace mysli\js\jquery;

__use(__namespace__, '
    mysli.framework.fs/fs,file
    mysli.framework.exception/* -> framework\exception\*
    mysli.util.curl
    mysli.util.config
    mysli.web.assets
');

class jquery
{
    /**
     * Get jQuery link. This will fetch file if not there already
     * (and if `local` is set to true);
     * @param  string  $version costume version...
     * @param  boolean $dev true|false|null (read from config)
     * @return string
     */
    static function get_link($version=null, $dev=null)
    {
        $c = config::select('mysli.js.jquery');

        if ($dev === null)
        {
            $dev = $c->get('development', false);
        }

        if ($version === null)
        {
            $version = self::get_version($dev);
        }
        else
        {
            $version .= ($dev ? '' : '.min');
        }

        if ($c->get('local'))
        {
            $remote_url = $c->get('remote_url');

            if (!self::has_local($version))
            {
                $dest = self::get_path($version);

                if (!self::fetch_library($version, $dev, $dest, $remote_url))
                {
                    throw new framework\exception\fs('Failed to fetch jQuery.');
                }
            }

            $url = assets::get_public_url('mysli.js.jquery');

            return $url."/jquery-{$version}.js";
        }
        else
        {
            return str_replace('{version}', $version, $c->get('remote_url'));
        }
    }
    /**
     * Get full absolute path to the jQuery file.
     * This will return path even if file doesn't exists.
     * @param  string  $version costume version...
     * @return string
     */
    static function get_path($version)
    {
        return fs::ds(
            assets::get_public_path('mysli.js.jquery'),
            "/jquery-{$version}.js"
        );
    }
    /**
     * Check if local version of file exists.
     * @param  string  $version costume version...
     * @param  boolean  $dev true|false
     * @return boolean
     */
    static function has_local($version)
    {
        $file = self::get_path($version);
        return file::exists($file);
    }
    /**
     * Get version of jQuery as set in config.
     * @param  boolean $dev true|false
     * @return string
     */
    static function get_version($dev)
    {
        $c = config::select('mysli.js.jquery');
        $version = $c->get($dev ? 'dev_version' : 'version');

        return $dev ? $version : $version.($dev?'':'.min');
    }
    /**
     * Fetch jQuery using curl
     * @param  string  $version
     * @param  boolean $dev weather to fetch dev version of library.
     * @param  string  $destination
     * @param  string  $remote_url
     * @return boolean
     */
    private static function fetch_library(
        $version, $dev, $destination, $remote_url)
    {
        if (!\core\pkg::is_enabled('mysli.util.curl'))
        {
            throw new framework\exception\not_found(
                'You need to enable `mysli/framework/curl` '.
                'to fetch jQuery and save it locally.'
            );
        }

        $url = str_replace('{version}', $version, $remote_url);
        $jquery = curl::get($url);

        if ($jquery)
        {
            return file::write($destination, $jquery);
        }
    }
}
