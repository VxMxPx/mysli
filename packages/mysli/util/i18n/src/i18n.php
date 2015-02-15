<?php

namespace mysli\util\i18n;

__use(__namespace__, '
    mysli.framework.json
    mysli.framework.fs/file,fs
    mysli.framework.exception/* as framework\exception\*
');

class i18n {
    // created instances of translator
    private static $cache = [];
    // default languages
    private static $default_languages = [];

    /**
     * Set default languages to be used when translator is constructed.
     * @param string $primary
     * @param string $secondary
     */
    static function set_default_language($primary, $secondary) {
        self::$default_languages = [$primary, $secondary];
    }
    /**
     * Create cache for current package.
     * @return boolean
     */
    static function create_cache($package, $folder='i18n') {
        $dir = fs::pkgpath($package, $folder);
        if (!file::exists($dir)) {
            throw new framework\exception\not_found(
                "Cannot create cache. Directory doesn't exists: `{$dir}`.", 1);
        }

        $collection = [];

        $files = scandir($dir);
        foreach ($files as $file) {
            if (substr($file, -3) !== '.mt') {
                continue;
            }
            $collection[substr($file, 0, -3)] = parser::parse(
                file_get_contents(fs::ds($dir, $file))
            );
        }

        // file to which parsed languages will be saved
        $file = self::package_to_filename($package);
        return json::encode_file($file, $collection);
    }
    /**
     * Remove cache for current package.
     * @return boolean
     */
    static function remove_cache($package) {
        $file = self::package_to_filename($package);
        if (file::exists($file)) {
            return unlink($file);
        } else return true;
    }
    /**
     * Return translator object.
     * @param  string $package
     * @param  mixed  $translate
     * @param  mixed  $param
     * @return mysli\util\i18n\translator or string if $translate is present
     */
    static function select($package, $translate=null, $variable=[]) {
        // Get translator + dictionary if not set yet
        if (!isset(self::$cache[$package])) {
            $cache_filename = self::package_to_filename($package);

            if (file::exists($cache_filename)) {
                $dictionary = json::decode_file($cache_filename, true);
            } else {
                $dictionary = [];
            }

            self::$cache[$package] = new translator($dictionary,
                                            self::$default_languages[0],
                                            self::$default_languages[1]);
        }

        $translator = self::$cache[$package];

        // If we have $translate we'll return translation otherwise translator
        return $translate
                    ? $translator->translate($translate, $variable)
                    : $translator;
    }

    /**
     * Convert package name, to full name for package's dictionary.
     * @param  string $package
     * @return string
     */
    private static function package_to_filename($package) {
        return fs::datpath('mysli/util/i18n/',
                           str_replace('/', '.', $package).'.json');
    }
}
