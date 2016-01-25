<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;

#: Test Escape String Block
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$template = <<<'TEMPLATE'
::for item in 'param'|funct
::/for

    ::if one !== 'value'
    ::/if
TEMPLATE;
$parser = new parser();
return assert::equals(
    $parser->process($template),
    <<<'EXPECT'
<?php foreach ($tplp_func_funct('param') as $item): ?>
<?php endforeach; ?>
    <?php if ($one !== 'value'): ?>
    <?php endif; ?>
EXPECT
);
