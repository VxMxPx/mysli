<?php

namespace mysli\toolkit; class __setup
{
    private static $default_config = [
        // Cookies default configurations
        'cookie.prefix'      => ['string',  ''],
        'cookie.encrypt'     => ['boolean', false],
        'cookie.encrypt_key' => ['string',  null],
        'cookie.sign'        => ['boolean', false],
        'cookie.sign_key'    => ['string',  null]
    ];

    /**
     * When toolkit is enabled, default folders and files needs to be created.
     * At this point toolkit is not initialized yet, and will ignore `__use`
     * instructions.
     *
     * There's an expectation that following directories already exists:
     * BINPATH, APPPATH, PUBPATH, as they were created by installer prior to
     * this.
     * --
     * @param string $apppath Absolute application root path.
     * @param string $binpath Absolute binaries root path.
     * @param string $pubpath Absolute public path.
     * --
     * @throws \Exception 10 Cannot create temporary directory.
     * @throws \Exception 20 Cannot create configuration directory.
     * @throws \Exception 21 Couldn't create `pkg` directory in configuration path.
     * @throws \Exception 30 Cannot create toolkit.pkg.list.
     * @throws \Exception 40 Cannot create the tookit.php file.
     * @throws \Exception 50 Cannot create the tookit.events.json file.
     * @throws \Exception 60 Cannot create index.php file.
     * --
     * @return boolean
     */
    static function enable($apppath, $binpath, $pubpath)
    {
        $tmppath = "{$apppath}/tmp";
        $cfgpath = "{$apppath}/configuration";

        /*
        Create temporary directory if not there already.
         */
        if (!file_exists($tmppath))
        {
            if (!mkdir($tmppath, 0755, true))
                throw new \Exception(
                    "Couldn't create temporary directory.", 10
                );
        }

        /*
        Create configuration directory.
         */
        if (!file_exists($cfgpath))
        {
            if (!mkdir($cfgpath, 0755, true))
                throw new \Exception(
                    "Couldn't create configuration directory", 20
                );
        }

        /*
        Create pkg directory in configuration.
         */
        if (!file_exists("{$cfgpath}/pkg"))
        {
            if (!mkdir("{$cfgpath}/pkg", 0755, true))
                throw new \Exception(
                    "Couldn't create `pkg` directory in configuration path.", 21
                );
        }

        /*
        Write toolkit pkg list.
         */
        // Add self and dot packages to the enabled list.
        $pkg_list =
            'mysli.toolkit '.self::get_version($binpath, 'mysli.toolkit')."\n".
            'dot '.self::get_version($binpath, 'dot');

        if (!file_put_contents("{$cfgpath}/toolkit.pkg.list", $pkg_list))
            throw new \Exception(
                "Cannot create `{$cfgpath}/toolkit.pkg.list` file.", 30
            );

        /*
        Write toolkit.php file, containing toolkit's unique ID, for easy and
        dynamic loading of toolkit from index.
         */
        $toolkit_load =
            "<?php\n\ndefine(\n    'TOOLKIT_LOAD',\n    ".
            "'mysli.toolkit:::__init:::mysli\\toolkit\\__init::__init'\n);";

        if (!file_put_contents("{$cfgpath}/toolkit.php", $toolkit_load))
            throw new \Exception(
                "Cannot create `{$cfgpath}/toolkit.php` file.", 40
            );

        /*
        Create toolkit events file.
         */
        if (!file_put_contents("{$cfgpath}/toolkit.events.json", '{}'))
            throw new \Exception(
                "Cannot create `{$cfgpath}`/toolkit.events.json` file.", 50
            );

        /*
        Write index.php file
         */
        $index = base64_decode(trim(self::index_php));
        if (!file_put_contents("{$pubpath}/index.php", $index))
            throw new \Exception("Cannot create `index.php` file.", 60);

        /*
        Write all default configurations
         */
        file_put_contents(
            "{$cfgpath}/pkg/mysli.toolkit.json",
            json_encode(self::$default_config)
        );

        /*
        Done.
         */
        return true;
    }

    /*
    --- Private ----------------------------------------------------------------
     */

    /**
     * Read particular package's version, without utilizing `ym` class.
     * --
     * @param string $binpath Packages root directory.
     * @param string $package Package of which version should be acquired.
     * --
     * @throws \Exception  9 Couldn't find package.
     * @throws \Exception 10 Couldn't find mysli.pkg.ym file.
     * @throws \Exception 20 Couldn't find version key in mysli.pkg.ym file.
     * --
     * @return integer
     */
    static function get_version($binpath, $package)
    {
        /*
        Check if file needs to be loaded from phar.
         */
        if (file_exists("{$binpath}/{$package}.phar"))
            $file = realpath("phar://{$binpath}/{$package}.phar/mysli.pkg.ym");
        else if (file_exists("{$binpath}/{$package}"))
            $file = realpath("{$binpath}/{$package}/mysli.pkg.ym");
        else
            throw new \Exception(
                "Couldn't find package: `{$package}` in `{$binpath}`.", 9
            );

        if (!$file)
            throw new \Exception(
                "Couldn't find `mysli.pkg.ym` file to read version.", 10
            );

        // Get mysli.pkg contents
        $meta = file_get_contents($file);

        // Find `version: <number>` line in the file.
        if (preg_match(
            '/^[ \t]*?version[ \t]*?:[ \t]*?([0-9]+)$/ms',
            $meta,
            $matches))
        {
            $version = (int) $matches[1];
        }
        else
        {
            throw new \Exception(
                "Couldn't find `version` key in `mysli.pkg.ym`.", 20
            );
        }

        return $version;
    }

    /**
     * Index.php
     */
    const index_php = <<<'INDEX'
PD9waHAKCm5hbWVzcGFjZSBteXNsaVx0b29sa2l0XGluZGV4OwoKLyoKQXQgbGVhc3QgdGhpcyB2ZXJz
aW9uIGlzIG5lZWRlZCB0byBwcm9jZWVkLgogKi8KY29uc3QgTkVFRF9WRVJTSU9OID0gJzUuNi4wJzsK
Ci8qClNldCB0aW1lem9uZSB0byBVVEMgdGVtcG9yYXJpbHkKICovCmRhdGVfZGVmYXVsdF90aW1lem9u
ZV9zZXQoJ1VUQycpOwoKLyoKUmVwb3J0IGFsbCBlcnJvcnMuCiAqLwplcnJvcl9yZXBvcnRpbmcoRV9B
TEwpOwovKgpGb3Igbm93IGRpc3BsYXkgYWxsIGVycm9yIHRvbywgdGhpcyBtaWdodCBiZSBjaGFuZ2Vk
IGJ5IHRvb2xraXQgbGF0ZXIuCiAqLwppbmlfc2V0KCdkaXNwbGF5X2Vycm9ycycsIHRydWUpOwoKLyoK
Q2hlY2sgaWYgY3VycmVudCBQSFAgdmVyc2lvbiBpcyBzdWZmaWNpZW50IHRvIHByb2NlZWQuCiAqLwpp
ZiAoISh2ZXJzaW9uX2NvbXBhcmUoUEhQX1ZFUlNJT04sIE5FRURfVkVSU0lPTikgPj0gMCkpCiAgICB0
cmlnZ2VyX2Vycm9yKAogICAgICAgICdQSFAgbmVlZHMgdG8gYmUgYXQgbGVhc3QgdmVyc2lvbiBgJy5O
RUVEX1ZFUlNJT04uJ2AgJy4KICAgICAgICAnWW91ciB2ZXJzaW9uOiBgJy5QSFBfVkVSU0lPTi4nYCcs
CiAgICAgICAgRV9VU0VSX0VSUk9SCiAgICApOwoKLyoKQ2hlY2sgaWYgdGhlcmUgYXJlIGFueSBwYXRo
IGluc3RydWN0aW9ucyBpbiB0aGlzIGZvbGRlciwgaWYgbm90LCBsb2MgZmlsZSBpcwpsb2NhdGVkIG9u
ZSBsZXZlbCBiZWxsb3cgY3VycmVudCBkaXJlY3RvcnkuCiAqLwppZiAoZmlsZV9leGlzdHMoX19ESVJf
Xy4nL215c2xpLmxvYy5waHAnKSkKewogICAgaW5jbHVkZSBfX0RJUl9fLicvbXlzbGkubG9jLnBocCc7
CiAgICBpZiAoIWRlZmluZWQoJ01ZU0xJX0xPQ19JTkRFWF9BUFBQQVRIJykpCiAgICAgICAgdHJpZ2dl
cl9lcnJvcigKICAgICAgICAgICAgIkV4cGVjdGVkIGNvbnN0IG5vdCBmb3VuZDogYE1ZU0xJX0xPQ19J
TkRFWF9BUFBQQVRIYCBpbiAiLgogICAgICAgICAgICAiYC4vbXlzbGkubG9jLnBocGAiLAogICAgICAg
ICAgICBFX1VTRVJfRVJST1IKICAgICAgICApOwoKICAgICRhcHBwYXRoID0gcmVhbHBhdGgoX19ESVJf
Xy5NWVNMSV9MT0NfSU5ERVhfQVBQUEFUSCk7Cn0KZWxzZQp7CiAgICAkYXBwcGF0aCA9IGRpcm5hbWUo
X19ESVJfXyk7Cn0KCiRteXNsaV9sb2MgPSAieyRhcHBwYXRofS9teXNsaS5sb2MucGhwIjsKaWYgKCFm
aWxlX2V4aXN0cygkbXlzbGlfbG9jKSkKICAgIHRyaWdnZXJfZXJyb3IoIkZpbGUgbm90IGZvdW46IGBt
eXNsaS5sb2MucGhwYCBpbiBBUFBQQVRIIiwgRV9VU0VSX0VSUk9SKTsKCmluY2x1ZGUgJG15c2xpX2xv
YzsKCiRiaW5wYXRoID0gcmVhbHBhdGgoJGFwcHBhdGguJy8nLk1ZU0xJX0xPQ19CSU5QQVRIKTsKJHB1
YnBhdGggPSBfX0RJUl9fOwoKaWYgKCEkYmlucGF0aCkKICAgIHRyaWdnZXJfZXJyb3IoCiAgICAgICAg
IkJpbiBwYXRoIG5vdCBmb3VuZCBpbjogYHskYXBwcGF0aH1gIGxvb2tpbmcgZm9yOiBgIi4KICAgICAg
ICBNWVNMSV9MT0NfQklOUEFUSC4iYC4iLAogICAgICAgIEVfVVNFUl9FUlJPUgogICAgKTsKCi8qCkxv
YWQgdG9vbGtpdCBub3cuCiAqLwokdG9vbGtpdF9jb25mID0gInskYXBwcGF0aH0vY29uZmlndXJhdGlv
bi90b29sa2l0LnBocCI7CmlmICghZmlsZV9leGlzdHMoJHRvb2xraXRfY29uZikpCiAgICB0cmlnZ2Vy
X2Vycm9yKAogICAgICAgICJUb29sa2l0IGNvbmZpZ3VyYXRpb24gbm90IGZvdW5kIGluIGB7YXBwcGF0
aH0vY29uZmlndXJhdGlvbi90b29sa2l0LnBocGAiLAogICAgICAgIEVfVVNFUl9FUlJPUgogICAgKTsK
Ci8vIFRvb2xraXQgY29uZiB3aWxsIGRlZmluZSBUT09MS0lUX0xPQUQsIHdoaWNoIHdpbGwgaG9sZCBp
bmZvcm1hdGlvbiBvbiBob3cgdG8KLy8gaW5pdGlhbGl6ZSB0b29sa2l0LiBUaGlzIGZpbGUgd2lsbCBh
bHNvIGFsbG93IHRvb2xraXQgdG8gYmUgcmVwbGFjZSBieSBhbnkKLy8gb3RoZXIgdmVuZG9yLgppbmNs
dWRlICR0b29sa2l0X2NvbmY7CgovLyBUT09MS0lUX0xPQUQgaXMgd3JpdHRlbiBpbiBmb3JtYXQ6Ci8v
IGJpbmFyeV9uYW1lOjo6aW5pdF9maWxlbmFtZV90b19sb2FkOjo6bmFtZXNwYWNlZF9tZXRob2RfdG9f
Y2FsbAovLyBFeGFtcGxlOiBteXNsLnRvb2xraXQ6Ojp0b29sa2l0LmluaXQ6OjpteXNsaVx0b29sa2l0
XHRvb2xraXRfaW5pdDo6X19pbml0Cmxpc3QoJHRrX2JpbiwgJHRrX2ZpbGUsICR0a19jYWxsKSA9IGV4
cGxvZGUoJzo6OicsIFRPT0xLSVRfTE9BRCk7CgovLyBUb29sa2l0IGJhc2UgZGlyZWN0b3J5CiR0a19k
aXIgPSAieyRiaW5wYXRofS97JHRrX2Jpbn0iOwoKLy8gSWYgaXQgZG9lc24ndCBleGlzdHMsIGl0IG1p
Z2h0IGJlIHBoYXIKaWYgKCFmaWxlX2V4aXN0cygkdGtfZGlyKSkKewogICAgLy8gSWYgbm90IHBoYXIs
IHRoZW4gc29tZXRoaW5nIHdlbnQgd3JvbmcKICAgIGlmICghZmlsZV9leGlzdHMoJHRrX2Rpci4nLnBo
YXInKSkKICAgICAgICB0cmlnZ2VyX2Vycm9yKCJUb29sa2l0IG5vdCBmb3VuZCBgeyR0a19kaXJ9YC4i
LCBFX1VTRVJfRVJST1IpOwogICAgZWxzZQogICAgICAgICR0a19kaXIgPSAicGhhcjovL3skdGtfZGly
fS5waGFyIjsKfQoKLy8gVG9vbGtpdCBmaWxlLCB3aGljaCBjb250YWlucyBpbml0IGNsYXNzLgokdGtf
ZmlsZSA9ICJ7JHRrX2Rpcn0vbGliL3skdGtfZmlsZX0ucGhwIjsKCmlmICghZmlsZV9leGlzdHMoJHRr
X2ZpbGUpKQogICAgdHJpZ2dlcl9lcnJvcigKICAgICAgICAiVG9vbGtpdCBgaW5pdGAgZmlsZSBub3Qg
Zm91bmQ6IGB7JHRrX2ZpbGV9YC4iLAogICAgICAgIEVfVVNFUl9FUlJPUgogICAgKTsKCmluY2x1ZGUg
JHRrX2ZpbGU7CgpsaXN0KCR0a19jbGFzcywgJHRrX21ldGhvZCkgPSBleHBsb2RlKCc6OicsICR0a19j
YWxsLCAyKTsKCmlmICghY2xhc3NfZXhpc3RzKCR0a19jbGFzcywgZmFsc2UpKQogICAgdHJpZ2dlcl9l
cnJvcigiVG9vbGtpdCBjbGFzcyBub3QgZm91bjogYHskdGtfY2xhc3N9YCIsIEVfVVNFUl9FUlJPUik7
CgovLyBfX2luaXQgdG9vbGtpdApjYWxsX3VzZXJfZnVuY19hcnJheSgkdGtfY2FsbCwgWyRhcHBwYXRo
LCAkYmlucGF0aCwgJHB1YnBhdGhdKTsKCi8vIFJ1biB0b29sa2l0IGB3ZWJgCmNhbGxfdXNlcl9mdW5j
KCJ7JHRrX2NsYXNzfTo6d2ViIik7CgovLyBEb25lLgovLyBFT0YK
INDEX;

}
