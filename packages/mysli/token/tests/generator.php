<?php

namespace Mysli\Token;

function generate_test_data()
{
    if (file_exists(__DIR__ . '/dummy')) {
        `rm -rf dummy`;
    }
    `mkdir -p dummy/mysli.token`;
    `touch dummy/mysli.token/registry.json`;
}
