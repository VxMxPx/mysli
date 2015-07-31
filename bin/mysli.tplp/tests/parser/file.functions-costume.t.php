<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;
use mysli\toolkit\fs\fs;
use mysli\toolkit\fs\file;

$file = <<<'FILE'
<html>
<body>
    {variable|max:var|var_fnct:true}
    {|var_funct:true}
    {|blog/func:true}
</body>
</html>
FILE;
file::write(fs::tmppath('dev.test/~test.tpl.html'), $file);


#: After
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
file::remove(fs::tmppath('dev.test/~test.tpl.html'));


#: Test Functions Costume
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$processed = parser::file('~test.tpl.html', fs::tmppath('dev.test'));
return assert::equals(
    $processed,
    <<<'EXPECT'
<?php
namespace tplp\template\test;
?><html>
<body>
    <?php echo $tplp_func_var_fnct(max($variable, $var), true); ?>
    <?php echo $tplp_func_var_funct(true); ?>
    <?php echo blog::func(true); ?>
</body>
</html>
EXPECT
);
