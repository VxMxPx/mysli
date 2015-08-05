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
$parser = new parser(fs::tmppath('dev.test'));
return assert::equals(
    $parser->template($file),
    <<<'EXPECT'
<?php foreach ($tplp_func_funct('param') as $item): ?>
<?php endforeach; ?>
    <?php if ($one !== 'value'): ?>
    <?php endif; ?>
EXPECT
);
