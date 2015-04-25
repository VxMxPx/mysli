<?php

namespace mysli\js\common\setup;

__use(__namespace__, '
    mysli.web.assets
');

function enable()  { return assets::publish('mysli.js.common'); }
function disable() { return assets::destroy('mysli.js.common'); }
