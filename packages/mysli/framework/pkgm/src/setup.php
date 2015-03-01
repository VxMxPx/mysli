<?php

namespace mysli\framework\pkgm\setup;

function enable()
{
    $selfrelease = __DIR__;
    if (substr($selfrelease, 0, 7) === 'phar://')
        $selfrelease = substr( basename(dirname($selfrelease)), 0, -5);
    else
        $selfrelease = 'mysli/framework/pkgm';

    $std_list = [
        'mysli.framework.pkgm'      => $selfrelease,
        'mysli.framework.fs'        => null,
        'mysli.framework.json'      => null,
        'mysli.framework.ym'        => null,
        'mysli.framework.exception' => null,
        'mysli.framework.type'      => null,
    ];

    // Find essential packages
    foreach ($std_list as $qname => &$qrelease)
        if (!$qrelease)
            $qrelease = __discover_package($qname, MYSLI_PKGPATH);

    unset($qrelease);

    // Add packages to the list...
    foreach ($std_list as $qname => $qrelease)
        \core\pkg::add($qname, ['release' => $qrelease, 'package' => $qname]);

    // Include self
    __use(__namespace__, './pkgm');

    $std_list[MYSLI_CORE_PKG] = MYSLI_CORE_PKG_REL;

    // Update!
    foreach ($std_list as $qname => $qrelease)
        \core\pkg::update($qname, pkgm::meta($qrelease, true));

    // Enable + Disable to update to proper pkgm format
    foreach ($std_list as $qname => $qrelease)
        pkgm::disable($qrelease) + pkgm::enable($qrelease);

    // Finally add self to the list of boot packages
    \core\pkg::set_boot('pkgm', 'mysli.framework.pkgm');
    \core\pkg::write();

    // Came so far?
    return true;
}

/**
 * Discover closest version of package.
 * @param  string $name
 * @param  string $pkgpath
 * @return string full package's name or null
 */
function __discover_package($name, $pkgpath)
{
    $regex = '/^'.preg_quote($name).'-r.*?\\.phar$/';

    foreach (scandir($pkgpath) as $file)
        if (preg_match($regex, $file))
            return substr($file, 0, -5);

    // Perhaps we have source?
    $name = str_replace('.', '/', $name);
    if (file_exists("{$pkgpath}/{$name}/mysli.pkg.ym"))
        return $name;

}
