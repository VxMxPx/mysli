--TEST--
--VIRTUAL (test.tplp)--
<html>
<body>
    {variable|max:var|var_fnct:true}
    {|var_funct:true}
    {|blog/func:true}
</body>
</html>
--FILE--
<?php
use mysli\util\tplp\parser;
print_r(parser::file('test.tplp', __DIR__));
?>
--EXPECT--
<?php
namespace tplp\generic\test;
?><html>
<body>
    <?php echo $tplp_func_var_fnct(max($variable, $var), true); ?>
    <?php echo $tplp_func_var_funct(true); ?>
    <?php echo blog::func(true); ?>
</body>
</html>
