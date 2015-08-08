<?php

namespace mysli\i18n; class i18n
{
    const __use = '
        .{ exception.i18n }
        mysli.toolkit.{
            pkg,
            fs.fs -> fs,
            fs.dir -> dir,
            fs.file -> file,
            type.arr_path -> arrp
        }
    ';

    // created instances of translator
    private static $cache = [];

    /**
     * Return translator object, or translate particular string.
     * --
     * @param mixed $id
     *        String Translator's ID.
     *        Array  [ string $id, string $primary, string $secondary ]
     *
     * @param mixed $translate
     *        Optional, string to trasnalte.
     *
     * @param mixed $param
     *        Optiona, if `$translate` is provided,
     *        this sets variables to be used in translation.
     * --
     * @throws mysli\i18n\exception\i18n 10 Invalid ID.
     * --
     * @return mixed
     *         mysli\util\i18n\translator or
     *         string if $translate is present
     */
    static function select($id, $translate=null, $variable=[])
    {
        if (is_array($id))
        {
            if (count($id) === 3)
                list($id, $primary, $secondary) = $id;
            else
                throw new exception\i18n(
                    "Invalid ID. Required either string or array: ".
                    "`[ string id, string primary, string secondary ]`.", 10
                );
        }
        else
        {
            $primary = $secondary = null;
        }

        if (!isset(static::$cache[$id]))
        {
            static::$cache[$id] = new translator($primary, $secondary);
        }

        $translator = static::$cache[$id];

        if ($translate !== null)
            return $translator->translate($translate, $variable);
        else
            return $translator;
    }


    /**
     * Get full absolute path to the root of i18n, from package name.
     * This will inspect mysli.pkg.ym if costume path is set.
     *
     * If package not found, null will be returned.
     * --
     * @param string $package
     * --
     * @throws mysli\i18n\exception\i18n 10 No such package.
     * --
     * @return string
     */
    static function get_path($package)
    {
        if (!pkg::exists($package))
            throw new exception\i18n("No such package: `{$package}`.", 10);

        $meta = pkg::get_meta($package);
        $path = arrp::get($meta, 'i18n.path', 'assets/i18n');

        return fs::pkgreal($path);
    }

    /**
     * Get a full filename for temporary file, from a path+filename.
     * Result example, for a file named: `si`
     * cb647a447c6841d5e5840b44194ed0a4-si.json
     * --
     * @param string $file
     * @param string $root
     * --
     * @return string
     */
    static function tmpname($file, $root)
    {
        return
            md5("{$root}{$file}.lng").
            '-'.
            str_replace('/', '-d-', $file).'.json';
    }
}
