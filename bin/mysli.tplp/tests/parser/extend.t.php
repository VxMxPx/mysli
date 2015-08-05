<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;
use mysli\toolkit\fs\fs;

#: Define File
$base = <<<'TEST'
::extend _layout set content
<div>
    Some content here...
</div>
TEST;

$_layout = <<<'_LAYOUT'
<!DOCTYPE html>
<html>
<head>
    <title>{title}</title>
</head>
<body>
    ::print content
</body>
</html>
_LAYOUT;

#: Test Extend
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#: Use File
$parser = new parser(fs::tmppath('dev.test'));
$parser->replace('_layout.tpl.php', $parser->template($_layout));
$parsed = $parser->template($base);
return assert::equals(
    $parser->extend($parsed),
    <<<'EXPECT'
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $title; ?></title>
</head>
<body>
<div>
    Some content here...
</div>
</body>
</html>
EXPECT
);
