<?php

namespace mysli\js\common;

__use(__namespace__, '
    mysli.web.assets
');

class __init
{
    static function enable()  { return assets::publish('mysli.js.common'); }
    static function disable() { return assets::destroy('mysli.js.common'); }
}
