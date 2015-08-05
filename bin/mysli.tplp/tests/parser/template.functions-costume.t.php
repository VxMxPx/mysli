<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;
use mysli\toolkit\fs\fs;

#: Define File
$file = <<<'FILE'
<html>
<body>
    {variable|max:var|var_fnct:true}
    {|var_funct:true}
    {|blog/func:true}
</body>
</html>
FILE;

#: Test Functions Costume
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#: Use File
$parser = new parser(fs::tmppath('dev.test'));
return assert::equals(
    $parser->template($file),
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
