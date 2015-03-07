<?php

namespace mysli\util\i18n;

__use(__namespace__, '
    mysli.framework.json
    mysli.framework.pkgm
    mysli.framework.fs/file,fs
    mysli.framework.type/arr_path -> arrp
    mysli.framework.exception/*   -> framework\exception\*
');

class i18n
{
    // created instances of translator
    private static $cache = [];
    // default languages
    private static $default_languages = [];

    /**
     * Set default languages to be used when translator is constructed.
     * @param string $primary
     * @param string $secondary
     */
    static function set_default_language($primary, $secondary)
    {
        self::$default_languages = [$primary, $secondary];
    }
    /**
     * Return translator object.
     * @param  string $package
     * @param  mixed  $translate
     * @param  mixed  $param
     * @return mysli\util\i18n\translator or string if $translate is present
     */
    static function select($package, $translate=null, $variable=[])
    {
        // Get translator + dictionary if not set yet
        if (!isset(self::$cache[$package]))
        {
            list($_, $dest) = self::get_paths($package);

            if (file::exists($dest))
            {
                $dictionary = json::decode_file($dest, true);
            }
            else
            {
                $dictionary = [];
            }

            self::$cache[$package] = new translator(
                $dictionary, self::$default_languages[0], self::$default_languages[1]
            );
        }

        $translator = self::$cache[$package];

        // If we have $translate we'll return translation otherwise translator
        return $translate
            ? $translator->translate($translate, $variable)
            : $translator;
    }
    /**
     * Get full source and destionation path for particular package.
     * @param  string  $package
     * @param  boolean $absolute return full absolute paths
     * @return array   [$source, $destination]
     */
    static function get_paths($package, $absolute=true)
    {
        $meta = pkgm::meta($package);
        $source = arrp::get($meta, 'i18n/source', 'i18n');
        $destination = arrp::get($meta, 'i18n/destination', '_dist');
        $destination .= '/i18n.json';

        if ($absolute)
        {
            $source = fs::pkgreal($package, $source);
            $destination = fs::pkgreal($package, $destination);
        }

        return [$source, $destination];
    }
}
