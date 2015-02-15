<?php

namespace mysli\framework\pkgm;

__use(__namespace__, '
    mysli.framework.fs/fs,dir,file
');


function __init() {
    pkgm::__init(
        fs::datpath('boot/packages.json'),
        fs::datpath('mysli/framework/pkgm/r.json')
    );
}
