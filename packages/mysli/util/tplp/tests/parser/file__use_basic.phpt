--TEST--
--VIRTUAL (test.tplp)--
::use mysli/cms/blog as mblog
<div>
    {variable}
</div>
--FILE--
<?php
use mysli\util\tplp\parser;
print_r(parser::file('test.tplp', __DIR__));
?>
--EXPECT--
<?php
namespace tplp\generic\test;
use mysli\cms\blog\tplp\util as mblog;
?><div>
    <?php echo $variable; ?>
</div>
