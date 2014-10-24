<?php

namespace mysli\util\i18n;

__use(__namespace__, '
    mysli/util/config
');

function __init() {
    i18n::set_default_language(
        config::select('mysli/util/i18n', 'primary_language', 'en'),
        config::select('mysli/util/i18n', 'secondary_language'));
}
