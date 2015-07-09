<?php

namespace mysli\toolkit; class __setup
{
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
        // Add self and dot packages to the list as being enabled.
        $pkg_list =
            'mysli.toolkit '.self::get_version($binpath, 'mysli.toolkit')."\n".
            'dot '.self::get_version($binpath, 'dot');

        if (!file_put_contents("{$cfgpath}/toolkit.pkg.list", $pkg_list))
            throw new \Exception(
                "Cannot create `{$cfgpath}/toolkit.pkg.list` file.", 30
            );

        /*
        Write toolkit.php file, containing toolkit's unique ID, for easy and
        dynamic loading for toolkit from index.
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
        Done.
         */
        return true;
    }

    /*
    --- Private ----------------------------------------------------------------
     */

    /**
     * Read this package's version, without utilizing `ym` class.
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
     * Index.php contents
     */
    const index_php = <<<INDEX
PD9waHAKCm5hbWVzcGFjZSBteXNsaVx0b29sa2l0XGluZGV4OwoKLyoKQXQgbGVhc3QgdGhpcyB2
ZXJzaW9uIGlzIG5lZWRlZCB0byBwcm9jZWVkLgogKi8KY29uc3QgTkVFRF9WRVJTSU9OID0gJzUu
Ni4wJzsKCi8qClNldCB0aW1lem9uZSB0byBVVEMgdGVtcG9yYXJpbHkKICovCmRhdGVfZGVmYXVs
dF90aW1lem9uZV9zZXQoJ1VUQycpOwoKLyoKUmVwb3J0IGFsbCBlcnJvcnMuCiAqLwplcnJvcl9y
ZXBvcnRpbmcoRV9BTEwpOwovKgpGb3Igbm93IGRpc3BsYXkgYWxsIGVycm9yIHRvbywgdGhpcyBt
aWdodCBiZSBjaGFuZ2VkIGJ5IHRvb2xraXQgbGF0ZXIuCiAqLwppbmlfc2V0KCdkaXNwbGF5X2Vy
cm9ycycsIHRydWUpOwoKLyoKQ2hlY2sgaWYgY3VycmVudCBQSFAgdmVyc2lvbiBpcyBzdWZmaWNp
ZW50IHRvIHByb2NlZWQuCiAqLwppZiAoISh2ZXJzaW9uX2NvbXBhcmUoUEhQX1ZFUlNJT04sIE5F
RURfVkVSU0lPTikgPj0gMCkpCiAgICB0cmlnZ2VyX2Vycm9yKAogICAgICAgICdQSFAgbmVlZHMg
dG8gYmUgYXQgbGVhc3QgdmVyc2lvbiBgJy5ORUVEX1ZFUlNJT04uJ2AgJy4KICAgICAgICAnWW91
ciB2ZXJzaW9uOiBgJy5QSFBfVkVSU0lPTi4nYCcsCiAgICAgICAgRV9VU0VSX0VSUk9SCiAgICAp
OwoKLyoKQ2hlY2sgaWYgdGhlcmUgYXJlIGFueSBwYXRoIGluc3RydWN0aW9ucyBpbiB0aGlzIGZv
bGRlciwgaWYgbm90LCBsb2MgZmlsZSBpcwpsb2NhdGVkIG9uZSBsZXZlbCBiZWxsb3cgY3VycmVu
dCBkaXJlY3RvcnkuCiAqLwppZiAoZmlsZV9leGlzdHMoX19ESVJfXy4nL215c2xpLmxvYy5waHAn
KSkKewogICAgaW5jbHVkZSBfX0RJUl9fLicvbXlzbGkubG9jLnBocCc7CiAgICBpZiAoIWRlZmlu
ZWQoJ01ZU0xJX0xPQ19JTkRFWF9BUFBQQVRIJykpCiAgICAgICAgdHJpZ2dlcl9lcnJvcigKICAg
ICAgICAgICAgIkV4cGVjdGVkIGNvbnN0IG5vdCBmb3VuZDogYE1ZU0xJX0xPQ19JTkRFWF9BUFBQ
QVRIYCBpbiAiLgogICAgICAgICAgICAiYC4vbXlzbGkubG9jLnBocGAiLAogICAgICAgICAgICBF
X1VTRVJfRVJST1IKICAgICAgICApOwoKICAgICRhcHBwYXRoID0gcmVhbHBhdGgoX19ESVJfXy5N
WVNMSV9MT0NfSU5ERVhfQVBQUEFUSCk7Cn0KZWxzZQp7CiAgICAkYXBwcGF0aCA9IGRpcm5hbWUo
X19ESVJfXyk7Cn0KCiRteXNsaV9sb2MgPSAieyRhcHBwYXRofS9teXNsaS5sb2MucGhwIjsKaWYg
KCFmaWxlX2V4aXN0cygkbXlzbGlfbG9jKSkKICAgIHRyaWdnZXJfZXJyb3IoIkZpbGUgbm90IGZv
dW46IGBteXNsaS5sb2MucGhwYCBpbiBBUFBQQVRIIiwgRV9VU0VSX0VSUk9SKTsKCmluY2x1ZGUg
JG15c2xpX2xvYzsKCiRiaW5wYXRoID0gcmVhbHBhdGgoJGFwcHBhdGguJy8nLk1ZU0xJX0xPQ19C
SU5QQVRIKTsKJHB1YnBhdGggPSBfX0RJUl9fOwoKaWYgKCEkYmlucGF0aCkKICAgIHRyaWdnZXJf
ZXJyb3IoCiAgICAgICAgIkJpbiBwYXRoIG5vdCBmb3VuZCBpbjogYHskYXBwcGF0aH1gIGxvb2tp
bmcgZm9yOiBgIi4KICAgICAgICBNWVNMSV9MT0NfQklOUEFUSC4iYC4iLAogICAgICAgIEVfVVNF
Ul9FUlJPUgogICAgKTsKCi8qCkxvYWQgdG9vbGtpdCBub3cuCiAqLwokdG9vbGtpdF9jb25mID0g
InskYXBwcGF0aH0vY29uZmlndXJhdGlvbi90b29sa2l0LnBocCI7CmlmICghZmlsZV9leGlzdHMo
JHRvb2xraXRfY29uZikpCiAgICB0cmlnZ2VyX2Vycm9yKAogICAgICAgICJUb29sa2l0IGNvbmZp
Z3VyYXRpb24gbm90IGZvdW5kIGluIGB7YXBwcGF0aH0vY29uZmlndXJhdGlvbi90b29sa2l0LnBo
cGAiLAogICAgICAgIEVfVVNFUl9FUlJPUgogICAgKTsKCi8vIFRvb2xraXQgY29uZiB3aWxsIGRl
ZmluZSBUT09MS0lUX0xPQUQsIHdoaWNoIHdpbGwgaG9sZCBpbmZvcm1hdGlvbiBvbiBob3cgdG8K
Ly8gaW5pdGlhbGl6ZSB0b29sa2l0LiBUaGlzIGZpbGUgd2lsbCBhbHNvIGFsbG93IHRvb2xraXQg
dG8gYmUgcmVwbGFjZSBieSBhbnkKLy8gb3RoZXIgdmVuZG9yLgppbmNsdWRlICR0b29sa2l0X2Nv
bmY7CgovLyBUT09MS0lUX0xPQUQgaXMgd3JpdHRlbiBpbiBmb3JtYXQ6Ci8vIGJpbmFyeV9uYW1l
Ojo6aW5pdF9maWxlbmFtZV90b19sb2FkOjo6bmFtZXNwYWNlZF9tZXRob2RfdG9fY2FsbAovLyBF
eGFtcGxlOiBteXNsLnRvb2xraXQ6Ojp0b29sa2l0LmluaXQ6OjpteXNsaVx0b29sa2l0XHRvb2xr
aXRfaW5pdDo6X19pbml0Cmxpc3QoJHRrX2JpbiwgJHRrX2ZpbGUsICR0a19jYWxsKSA9IGV4cGxv
ZGUoJzo6OicsIFRPT0xLSVRfTE9BRCk7CgovLyBUb29sa2l0IGJhc2UgZGlyZWN0b3J5CiR0a19k
aXIgPSAieyRiaW5wYXRofS97JHRrX2Jpbn0iOwoKLy8gSWYgaXQgZG9lc24ndCBleGlzdHMsIGl0
IG1pZ2h0IGJlIHBoYXIKaWYgKCFmaWxlX2V4aXN0cygkdGtfZGlyKSkKewogICAgLy8gSWYgbm90
IHBoYXIsIHRoZW4gc29tZXRoaW5nIHdlbnQgd3JvbmcKICAgIGlmICghZmlsZV9leGlzdHMoJHRr
X2Rpci4nLnBoYXInKSkKICAgICAgICB0cmlnZ2VyX2Vycm9yKCJUb29sa2l0IG5vdCBmb3VuZCBg
eyR0a19kaXJ9YC4iLCBFX1VTRVJfRVJST1IpOwogICAgZWxzZQogICAgICAgICR0a19kaXIgPSAi
cGhhcjovL3skdGtfZGlyfS5waGFyIjsKfQoKLy8gVG9vbGtpdCBmaWxlLCB3aGljaCBjb250YWlu
cyBpbml0IGNsYXNzLgokdGtfZmlsZSA9ICJ7JHRrX2Rpcn0vc3JjL3skdGtfZmlsZX0ucGhwIjsK
CmlmICghZmlsZV9leGlzdHMoJHRrX2ZpbGUpKQogICAgdHJpZ2dlcl9lcnJvcigKICAgICAgICAi
VG9vbGtpdCBgaW5pdGAgZmlsZSBub3QgZm91bmQ6IGB7JHRrX2ZpbGV9YC4iLAogICAgICAgIEVf
VVNFUl9FUlJPUgogICAgKTsKCmluY2x1ZGUgJHRrX2ZpbGU7CgpsaXN0KCR0a19jbGFzcywgJHRr
X21ldGhvZCkgPSBleHBsb2RlKCc6OicsICR0a19jYWxsLCAyKTsKCmlmICghY2xhc3NfZXhpc3Rz
KCR0a19jbGFzcywgZmFsc2UpKQogICAgdHJpZ2dlcl9lcnJvcigiVG9vbGtpdCBjbGFzcyBub3Qg
Zm91bjogYHskdGtfY2xhc3N9YCIsIEVfVVNFUl9FUlJPUik7CgovLyBfX2luaXQgdG9vbGtpdApj
YWxsX3VzZXJfZnVuY19hcnJheSgkdGtfY2FsbCwgWyRhcHBwYXRoLCAkYmlucGF0aCwgJHB1YnBh
dGhdKTsKCi8vIFJ1biB0b29sa2l0IGB3ZWJgCmNhbGxfdXNlcl9mdW5jKCJ7JHRrX2NsYXNzfTo6
d2ViIik7CgovLyBEb25lLgovLyBFT0YK
INDEX;

}
