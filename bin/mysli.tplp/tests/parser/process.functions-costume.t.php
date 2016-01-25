<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;

#: Test Functions Costume
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$template = <<<'TEMPLATE'
<html>
<body>
    {variable|max:var|var_fnct:true}
    {|var_funct:true}
    {|blog.func:true}
</body>
</html>
TEMPLATE;
$parser = new parser();
return assert::equals(
    $parser->process($template),
    <<<'EXPECT'
<html>
<body>
    <?php echo $tplp_func_var_fnct(max($variable, $var), true); ?>
    <?php echo $tplp_func_var_funct(true); ?>
    <?php echo blog::func(true); ?>
</body>
</html>
EXPECT
);
