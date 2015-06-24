--TEST--
--VIRTUAL (test.tplp)--
::for item in 'param'|funct
::/for

    ::if one !== 'value'
    ::/if
--FILE--
<?php
use mysli\util\tplp\parser;
print_r(parser::file('test.tplp', __DIR__));
?>
--EXPECT--
<?php
namespace tplp\generic\test;
?><?php foreach ($tplp_func_funct('param') as $item): ?>
<?php endforeach; ?>
    <?php if ($one !== 'value'): ?>
    <?php endif; ?>
