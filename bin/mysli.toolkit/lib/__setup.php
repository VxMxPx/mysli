<?php

namespace mysli\toolkit; class __setup
{
    /**
     * Default configurations.
     * --
     * @var array
     */
    private static $default_config = [
        // Cookies
        'cookie.prefix'      => [ 'string',  ''    ],
        'cookie.encrypt'     => [ 'boolean', false ],
        'cookie.encrypt_key' => [ 'string',  null  ],
        'cookie.sign'        => [ 'boolean', false ],
        'cookie.sign_key'    => [ 'string',  null  ],

        // JSON
        'json.encode_pretty_print' => [ 'boolean', true ],
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
     * @throws \Exception 22 Couldn't create content directory.
     * @throws \Exception 30 Cannot create toolkit.pkg.list.
     * @throws \Exception 40 Cannot create the tookit.php file.
     * @throws \Exception 50 Cannot create the tookit.events.json file.
     * @throws \Exception 51 Cannot create the routes.list file.
     * @throws \Exception 60 Cannot create index.php file.
     * --
     * @return boolean
     */
    static function enable($apppath, $binpath, $pubpath)
    {
        $tmppath = "{$apppath}/tmp";
        $cfgpath = "{$apppath}/configuration";
        $cntpath = "{$apppath}/content";

        /*
        Create temporary directory if not there already.
         */
        if (!file_exists($tmppath))
        {
            if (!mkdir($tmppath, 0755, true))
                throw new \Exception(
                    "Couldn't create `temporary` directory.", 10
                );
        }

        /*
        Create configuration directory.
         */
        if (!file_exists($cfgpath))
        {
            if (!mkdir($cfgpath, 0755, true))
                throw new \Exception(
                    "Couldn't create `configuration` directory", 20
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
        Create contents directory.
         */
        if (!file_exists($cntpath))
        {
            if (!mkdir($cntpath, 0755, true))
                throw new \Exception(
                    "Couldn't create `content` directory", 22
                );
        }

        /*
        Write toolkit pkg list.
         */
        // Add self to the enabled list.
        $pkg_list =
            'mysli.toolkit '.static::get_version($binpath, 'mysli.toolkit')." 0\n";

        if (!file_put_contents("{$cfgpath}/toolkit.pkg.list", $pkg_list))
            throw new \Exception(
                "Cannot create `{$cfgpath}/toolkit.pkg.list` file.", 30
            );

        /*
        Write toolkit.php file, containing toolkit's unique ID, for easy and
        dynamic loading of toolkit from index.
         */
        $toolkit_load = base64_decode(trim(self::toolkit_php));
        $toolkit_load = str_replace(
            [
                '{{{{TOOLKIT_FILE_CREATED_TIMESTAMP}}}}',
                '{{{{TOOLKIT_FILE_UPDATED_TIMESTAMP}}}}',
                '{{{{TOOLKIT_INIT}}}}',
                '{{{{TOOLKIT_CORE}}}}',
                '{{{{TOOLKIT_LOG}}}}',
                '{{{{TOOLKIT_PKG}}}}',
                '{{{{TOOLKIT_AUTOLOAD}}}}',
                '{{{{TOOLKIT_ERROR_HANDLING}}}}',
            ],
            [
                gmdate('c'),
                gmdate('c'),
                'mysli.toolkit __init',
                'mysli.toolkit toolkit',
                'mysli.toolkit log',
                'mysli.toolkit pkg',
                'mysli.toolkit autoloader',
                'mysli.toolkit error',
            ],
            $toolkit_load);

        if (!file_put_contents("{$cfgpath}/toolkit.php", $toolkit_load))
            throw new \Exception(
                "Cannot create `{$cfgpath}/toolkit.php` file.", 40
            );

        /*
        Create toolkit events file.
         */
        if (!file_put_contents("{$cfgpath}/toolkit.events.json", '{}'))
            throw new \Exception(
                "Cannot create `{$cfgpath}/toolkit.events.json` file.", 50
            );

        /*
        Create routes file.
         */
        if (!file_put_contents("{$cfgpath}/routes.list", "HIGH:\nMEDIUM:\nLOW:\n"))
            throw new \Exception(
                "Cannot create `{$cfgpath}/routes.list` file.", 51
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
            json_encode(static::$default_config)
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
        if (file_exists("phar://{$binpath}/{$package}.phar/mysli.pkg.ym"))
            $file = "phar://{$binpath}/{$package}.phar/mysli.pkg.ym";
        else if (file_exists("{$binpath}/{$package}/mysli.pkg.ym"))
            $file = "{$binpath}/{$package}/mysli.pkg.ym";
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
PD9waHAKCm5hbWVzcGFjZSBteXNsaVx0b29sa2l0XGluZGV4OwoKLy8gQXQgbGVhc3QgdGhpcyB2ZXJz
aW9uIGlzIG5lZWRlZCB0byBwcm9jZWVkCmNvbnN0IFRPT0xLSVRfTkVFRF9WRVJTSU9OID0gJzUuNi4w
JzsKCi8vIFJlcG9ydCBhbGwgZXJyb3JzCmVycm9yX3JlcG9ydGluZyhFX0FMTCk7CgovLyBDaGVjayBp
ZiBjdXJyZW50IFBIUCB2ZXJzaW9uIGlzIHN1ZmZpY2llbnQgdG8gcHJvY2VlZAppZiAoISh2ZXJzaW9u
X2NvbXBhcmUoUEhQX1ZFUlNJT04sIFRPT0xLSVRfTkVFRF9WRVJTSU9OKSA+PSAwKSkKewogICAgdHJp
Z2dlcl9lcnJvcigKICAgICAgICAnUEhQIG5lZWRzIHRvIGJlIGF0IGxlYXN0IHZlcnNpb24gYCcuVE9P
TEtJVF9ORUVEX1ZFUlNJT04uJ2AgJy4KICAgICAgICAnWW91ciB2ZXJzaW9uOiBgJy5QSFBfVkVSU0lP
Ti4nYCcsCiAgICAgICAgRV9VU0VSX0VSUk9SCiAgICApOwp9CgovLyBDaGVjayBpZiB0aGVyZSBhcmUg
YW55IHBhdGggaW5zdHJ1Y3Rpb25zIGluIHRoaXMgZm9sZGVyLAovLyBpZiBub3QsIExPQyBmaWxlIGlz
IGxvY2F0ZWQgb25lIGxldmVsIGJlbGxvdyBjdXJyZW50IGRpcmVjdG9yeQppZiAoZmlsZV9leGlzdHMo
X19ESVJfXy4nL215c2xpLmxvYy5waHAnKSkKewogICAgaW5jbHVkZSBfX0RJUl9fLicvbXlzbGkubG9j
LnBocCc7CgogICAgaWYgKCFkZWZpbmVkKCdNWVNMSV9MT0NfQVBQUEFUSCcpKQogICAgewogICAgICAg
IHRyaWdnZXJfZXJyb3IoCiAgICAgICAgICAgICJFeHBlY3RlZCBjb25zdCBub3QgZm91bmQ6IGBNWVNM
SV9MT0NfQVBQUEFUSGAgaW4gYC4vbXlzbGkubG9jLnBocGAiLAogICAgICAgICAgICBFX1VTRVJfRVJS
T1IKICAgICAgICApOwogICAgfQoKICAgICRhcHBwYXRoID0gcmVhbHBhdGgoX19ESVJfXy5NWVNMSV9M
T0NfQVBQUEFUSCk7Cn0KZWxzZQp7CiAgICAkYXBwcGF0aCA9IGRpcm5hbWUoX19ESVJfXyk7Cn0KCi8v
IFNldCBkaXJlY3Rvcnkgc2VwYXJhdG9yCiREUyA9IERJUkVDVE9SWV9TRVBBUkFUT1I7CgovLyBTZXQg
TE9DIGZpbGUgcGF0aAokbXlzbGlfbG9jID0gInskYXBwcGF0aH17JERTfW15c2xpLmxvYy5waHAiOwoK
Ly8gSWYgTE9DIGZpbGUgbm90IHRoZXJlLCB0aGVuIHF1aXQKaWYgKCFmaWxlX2V4aXN0cygkbXlzbGlf
bG9jKSkKICAgIHRyaWdnZXJfZXJyb3IoIkZpbGUgbm90IGZvdW46IGBteXNsaS5sb2MucGhwYCBpbiBB
UFBQQVRIIiwgRV9VU0VSX0VSUk9SKTsKCi8vIEdyYWIgTE9DIGZpbGUKaW5jbHVkZSAkbXlzbGlfbG9j
OwoKLy8gRGVmaW5lIHRlbXBvcmFyeSBCSU4gYW5kIFBVQiBwYXRocwokYmlucGF0aCA9IHJlYWxwYXRo
KCRhcHBwYXRoLiREUy5NWVNMSV9MT0NfQklOUEFUSCk7CiRwdWJwYXRoID0gX19ESVJfXzsKCi8vIENo
ZWNrIGlmIEJJTiBwYXRoIGV4aXN0cwppZiAoISRiaW5wYXRoKQp7CiAgICB0cmlnZ2VyX2Vycm9yKAog
ICAgICAgICJCaW4gcGF0aCBub3QgZm91bmQgaW46IGB7JGFwcHBhdGh9YCBsb29raW5nIGZvcjogYCIu
TVlTTElfTE9DX0JJTlBBVEguImAuIiwKICAgICAgICBFX1VTRVJfRVJST1IKICAgICk7Cn0KCi8vIExv
YWQgdG9vbGtpdCBtYWluIGNvbmZpZ3VyYXRpb24KJHRvb2xraXRfY29uZiA9ICJ7JGFwcHBhdGh9eyRE
U31jb25maWd1cmF0aW9ueyREU310b29sa2l0LnBocCI7CmlmICghZmlsZV9leGlzdHMoJHRvb2xraXRf
Y29uZikpCnsKICAgIHRyaWdnZXJfZXJyb3IoCiAgICAgICAgIlRvb2xraXQgY29uZmlndXJhdGlvbiBu
b3QgZm91bmQgaW4gYHskdG9vbGtpdF9jb25mfWAiLAogICAgICAgIEVfVVNFUl9FUlJPUgogICAgKTsK
fQoKLy8gVG9vbGtpdCBjb25maWd1cmF0aW9uIGZpbGUgd2lsbCBkZWZpbmUgVE9PTEtJVF9JTklULCB3
aGljaCB3aWxsIGhvbGQgaW5mb3JtYXRpb24KLy8gb24gaG93IHRvIGluaXRpYWxpemUgdG9vbGtpdC4g
VGhpcyBmaWxlIHdpbGwgYWxzbyBhbGxvdyB0b29sa2l0IHRvIGJlIHJlcGxhY2VkCi8vIGJ5IGFueSBv
dGhlciB2ZW5kb3IuCmluY2x1ZGUgJHRvb2xraXRfY29uZjsKCi8vIENvbmZpZ3VyYXRpb24gZmlsZSBj
b250YWlucyB0aW55IGNvcmUgbG9hZGVyIGZ1bmN0aW9uCi8vIGZvciBsb2FkaW5nIGVzc2VudGlhbCBj
b3JlIGZpbGVzLgokdG9vbGtpdF9jbGFzcyA9IHRvb2xraXRfY29yZV9sb2FkZXIoVE9PTEtJVF9JTklU
LCAkYmlucGF0aCk7CgovLyBfX2luaXQgdG9vbGtpdApjYWxsX3VzZXJfZnVuY19hcnJheSgkdG9vbGtp
dF9jbGFzcy4nOjpfX2luaXQnLCBbJGFwcHBhdGgsICRiaW5wYXRoLCAkcHVicGF0aF0pOwoKLy8gUnVu
IHRvb2xraXQgYHdlYmAKY2FsbF91c2VyX2Z1bmMoInskdG9vbGtpdF9jbGFzc306OndlYiIpOwoKLy8g
RU9GCg==
INDEX;

    const toolkit_php = <<<'TOOLKIT'
PD9waHAKCi8qClRoaXMgZmlsZSB3YXMgYXV0b21hdGljYWxseSBnZW5lcmF0ZWQgd2hlbiBzeXN0ZW0g
d2FzIGluaXRpYWxpemVkLgpOT1RFOiBWYWx1ZXMgY2FuIGJlIG1hbnVhbGx5IGNoYW5nZWQsIGJ1dCBr
ZWVwIHRoZW0gb25lIGRlZmluaXRpb24gcGVyIGxpbmUuCiAqLwoKLy8gTG9jIGZpbGUgY3JlYXRpb24g
dGltZXN0YW1wCmRlZmluZSgnVE9PTEtJVF9GSUxFX0NSRUFURURfVElNRVNUQU1QJywgJ3t7e3tUT09M
S0lUX0ZJTEVfQ1JFQVRFRF9USU1FU1RBTVB9fX19Jyk7CmRlZmluZSgnVE9PTEtJVF9GSUxFX1VQREFU
RURfVElNRVNUQU1QJywgJ3t7e3tUT09MS0lUX0ZJTEVfVVBEQVRFRF9USU1FU1RBTVB9fX19Jyk7Cgov
LyBQYXRocyBhcmUgc3BlY2lmaWVkIGFzOiB2ZW5kb3IucGFja2FnZSBmaWxlbmFtZSg9Y2xhc3MpCgov
LyBUb29sa2l0IGluaXQgY2xhc3MKZGVmaW5lKCdUT09MS0lUX0lOSVQnLCAne3t7e1RPT0xLSVRfSU5J
VH19fX0nKTsKCi8vIFRvb2xraXQgY29yZQpkZWZpbmUoJ1RPT0xLSVRfQ09SRScsICd7e3t7VE9PTEtJ
VF9DT1JFfX19fScpOwoKLy8gTG9nZ2VyIGNsYXNzCmRlZmluZSgnVE9PTEtJVF9MT0cnLCAne3t7e1RP
T0xLSVRfTE9HfX19fScpOwoKLy8gUGtnIGNsYXNzCmRlZmluZSgnVE9PTEtJVF9QS0cnLCAne3t7e1RP
T0xLSVRfUEtHfX19fScpOwoKLy8gUGF0aCBmb3IgbG9nIGZpbGVzCmRlZmluZSgnVE9PTEtJVF9MT0df
UEFUSCcsICd7dG1wfS9sb2dzJyk7CgovLyBXaGljaCBldmVudHMgdG8gd3JpdGUgdG8gZmlsZTogZGVi
dWcsaW5mbyxbbm90aWNlLHdhcm5pbmcsc2VjdXJpdHksZXJyb3IscGFuaWNdCmRlZmluZSgnVE9PTEtJ
VF9MT0dfV1JJVEUnLCAnbm90aWNlLHdhcm5pbmcsc2VjdXJpdHksZXJyb3IscGFuaWMnKTsKCi8vIEZv
ciB3aGljaCBldmVudHMgdG8gY3JlYXRlIGZ1bGwgcmVwb3J0OiBkZWJ1ZyxpbmZvLG5vdGljZSx3YXJu
aW5nLHNlY3VyaXR5LGVycm9yLFtwYW5pY10KLy8gSW4gc3VjaCBjYXNlIGEgbmV3IGZpbGUgd2lsbCBi
ZSBjcmVhdGVkLCBjb250YWluaW5nIGFsbCBtZXNzYWdlIGZvciBnaXZlbiByZXF1ZXN0LgpkZWZpbmUo
J1RPT0xLSVRfTE9HX1JFUE9SVCcsICAncGFuaWMnKTsKCi8vIChBdXRvKUxvYWRlciBjbGFzcwpkZWZp
bmUoJ1RPT0xLSVRfQVVUT0xPQUQnLCAne3t7e1RPT0xLSVRfQVVUT0xPQUR9fX19Jyk7CgovLyBFcnJv
ciBoYW5kZXIgY2xhc3MKZGVmaW5lKCdUT09MS0lUX0VSUk9SX0hBTkRMSU5HJywgJ3t7e3tUT09MS0lU
X0VSUk9SX0hBTkRMSU5HfX19fScpOwoKLy8gRGVmYXVsdCB0aW1lem9uZSB0byBiZSBzZXQgb24gYm9v
dC4gTGVhdmUgbnVsbCBmb3IgZGVmYXVsdCBhcyBzZXQgaW4gcGhwLmluaQpkZWZpbmUoJ1RPT0xLSVRf
REVGQVVMVF9USU1FWk9ORScsIG51bGwpOwoKLy8gV2VhdGhlciBlcnJvcnMgYXJlIHByaW50ZWQuIERv
IE5PVCBlbmFibGUgaXQgaW4gcHJvZHVjdGlvbi4KZGVmaW5lKCdUT09MS0lUX0RJU1BMQVlfRVJST1JT
JywgZmFsc2UpOwoKLyoqCiAqIEhlbHBlciBmdW5jdGlvbiBmb3IgbG9hZGluZyBjb3JlIGNsYXNzZXMg
ZGVmaW5lZCBpbiB0aGlzIGZpbGUuCiAqIC0tCiAqIEBwYXJhbSBzdHJpbmcgJGxvYWQKICogQHBhcmFt
IHN0cmluZyAkYmlucGF0aAogKiAtLQogKiBAcmV0dXJuIHN0cmluZyBSZXNvbHZlZCBjbGFzcyBuYW1l
IGlmIHN1Y2NlZWQuCiAqLwpmdW5jdGlvbiB0b29sa2l0X2NvcmVfbG9hZGVyKCRsb2FkLCAkYmlucGF0
aCkKewogICAgbGlzdCgkdmVuZG9yLCAkZmlsZSkgPSBleHBsb2RlKCcgJywgJGxvYWQpOwogICAgJERT
ID0gRElSRUNUT1JZX1NFUEFSQVRPUjsKCiAgICBpZiAoZmlsZV9leGlzdHMoInskYmlucGF0aH17JERT
fXskdmVuZG9yfS5waGFyIikpCiAgICAgICAgJGRpciA9ICJwaGFyOi8veyRiaW5wYXRofXskRFN9eyR2
ZW5kb3J9LnBoYXIiOwogICAgZWxzZWlmIChmaWxlX2V4aXN0cygieyRiaW5wYXRofXskRFN9eyR2ZW5k
b3J9IikpCiAgICAgICAgJGRpciA9ICJ7JGJpbnBhdGh9eyREU317JHZlbmRvcn0iOwogICAgZWxzZQog
ICAgICAgIHRyaWdnZXJfZXJyb3IoIk5vdCBmb3VuZCBgeyRiaW5wYXRofXskRFN9eyR2ZW5kb3J9YC4i
LCBFX1VTRVJfRVJST1IpOwoKICAgICRwYXRoID0gInskZGlyfXskRFN9bGlieyREU317JGZpbGV9LnBo
cCI7CiAgICAkY2xhc3MgPSBzdHJfcmVwbGFjZSgnLicsICdcXCcsICR2ZW5kb3IpIC4gJ1xcJyAuICRm
aWxlOwoKICAgIGlmICghZmlsZV9leGlzdHMoJHBhdGgpKQogICAgICAgIHRyaWdnZXJfZXJyb3IoImBp
bml0YCBmaWxlIG5vdCBmb3VuZDogYHskcGF0aH1gLiIsIEVfVVNFUl9FUlJPUik7CgogICAgaW5jbHVk
ZSAkcGF0aDsKCiAgICBpZiAoIWNsYXNzX2V4aXN0cygkY2xhc3MsIGZhbHNlKSkKICAgICAgICB0cmln
Z2VyX2Vycm9yKCJDbGFzcyBub3QgZm91bmQ6IGB7JGNsYXNzfWAiLCBFX1VTRVJfRVJST1IpOwogICAg
ZWxzZQogICAgICAgIHJldHVybiAkY2xhc3M7Cn0KCi8vIEVPRgo=
TOOLKIT;

}
