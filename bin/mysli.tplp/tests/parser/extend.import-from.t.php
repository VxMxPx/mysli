<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;
use mysli\toolkit\fs\fs;


#: Define Import
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$base = <<<'TEST'
<!DOCTYPE html>
<html>
<head>
    <title>{title}</title>
</head>
<body>
    ::import sidebar from _modules
</body>
</html>
TEST;

$_modules = <<<'_MODULES'
::module sidebar
<div class="sidebar">
    <p>Hello world!</p>
</div>
::/module
_MODULES;


#: Define Error
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$base = <<<'TEST'
<!DOCTYPE html>
<html>
<head>
    <title>{title}</title>
</head>
<body>
    ::import sidebar from non_existant
</body>
</html>
TEST;


#: Test Import From
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#: Use Import
$parser = new parser(fs::tmppath('dev.test'));
$parser->replace('_modules.tpl.php', $parser->template($_modules));
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
<div class="sidebar">
    <p>Hello world!</p>
</div>
</body>
</html>
EXPECT
);


#: Test Import From, Exception
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#: Use Error
#: Expect Exception mysli\tplp\exception\parser 10
$parser = new parser(fs::tmppath('dev.test'));
$parser->extend( $parser->template($base) );
