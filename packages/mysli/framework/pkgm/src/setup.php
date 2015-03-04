<?php

namespace mysli\framework\pkgm\setup;

function enable()
{
    $std_list = [
        'mysli.framework.pkgm'      => [],
        'mysli.framework.fs'        => [],
        'mysli.framework.json'      => [],
        'mysli.framework.ym'        => [],
        'mysli.framework.exception' => [],
        'mysli.framework.type'      => [],
    ];

    // Add packages to the list...
    foreach ($std_list as $qname => $qmeta)
        \core\pkg::add($qname, ['package' => $qname]);

    // Include self
    __use(__namespace__, './pkgm');

    $std_list[MYSLI_CORE] = ['package' => MYSLI_CORE];

    // Update!
    foreach ($std_list as $qname => $qmeta)
        \core\pkg::update($qname, pkgm::meta($qname, true));

    // Enable + Disable to update to proper pkgm format
    foreach ($std_list as $qname => $qmeta)
        pkgm::disable($qname) + pkgm::enable($qname);

    // Finally add self to the list of boot packages
    \core\pkg::set_boot('pkgm', 'mysli.framework.pkgm');
    \core\pkg::write();

    // Came so far?
    return true;
}
