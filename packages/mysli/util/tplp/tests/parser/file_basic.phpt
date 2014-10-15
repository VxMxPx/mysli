--TEST--
Test file input parsing.
--VIRTUAL (test.tplp)--
{variable}
--FILE--
<?php
use mysli\util\tplp\parser;
print_r(parser::file('test.tplp', __DIR__));
?>
--EXPECT--
<?php

?><?php echo $variable; ?>
