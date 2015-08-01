<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;
use mysli\toolkit\fs\fs;

#: Define File
$file = <<<'FILE'
::for item in 'param'|funct
::/for

    ::if one !== 'value'
    ::/if
FILE;

#: Test Escape String Block
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#: Use File
$processed = parser::file(
    '~test.tpl.html', fs::tmppath('dev.test'), [ '~test' => $file ]
);
return assert::equals(
    $processed,
    <<<'EXPECT'
<?php
namespace tplp\template\test;
?><?php foreach ($tplp_func_funct('param') as $item): ?>
<?php endforeach; ?>
    <?php if ($one !== 'value'): ?>
    <?php endif; ?>
EXPECT
);
