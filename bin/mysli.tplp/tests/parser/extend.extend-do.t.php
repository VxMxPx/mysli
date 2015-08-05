<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;
use mysli\toolkit\fs\fs;

#: Define File
$base = <<<'TEST'
::extend _layout set content do
    ::set styles
    <link rel="stylesheet" type="text/css" href="main.css">
    <link rel="stylesheet" type="text/css" href="mobile.css">
    ::/set
::/extend
<div>
    Some content here...
</div>
TEST;

$_layout = <<<'_LAYOUT'
<!DOCTYPE html>
<html>
<head>
    <title>{title}</title>
    ::print styles
</head>
<body>
    ::print content
</body>
</html>
_LAYOUT;

#: Test Extend Do
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
    <link rel="stylesheet" type="text/css" href="main.css">
    <link rel="stylesheet" type="text/css" href="mobile.css">
</head>
<body>
<div>
    Some content here...
</div>
</body>
</html>
EXPECT
);
