<?php

namespace mysli\web\token;

__use(__namespace__, '
    mysli.framework.fs
');

function __init()
{
    token::set_data_path(fs::datpath('mysli/web/token'));
    token::reload();
}
