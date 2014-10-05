--TEST--
Special functions.
--FILE--
<?php
use mysli\util\tplp\parser;

$input = <<<INPUT
<html>
<body>
    {variable|max:var|var_fnct:true}
    {|var_funct:true}
    {|blog/func:true}
</body>
</html>
INPUT;

print_r(parser::process($input));
?>
--EXPECT--
<html>
<body>
    <?php echo $tplp_func_var_fnct(max($variable, $var), true); ?>
    <?php echo $tplp_func_var_funct(true); ?>
    <?php echo blog::func(true); ?>
</body>
</html>
