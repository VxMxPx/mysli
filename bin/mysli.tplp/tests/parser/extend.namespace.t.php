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
    ::import _sidebar
</body>
</html>
TEST;

$_sidebar = <<<'_SIDEBAR'
<div class="sidebar">
    <p>Hello world!</p>
</div>
_SIDEBAR;


#: Define Error
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$base = <<<'TEST'
<!DOCTYPE html>
<html>
<head>
    <title>{title}</title>
</head>
<body>
    ::import _non_existant_file
</body>
</html>
TEST;


#: Test Import
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#: Use Import
$parser = new parser(fs::tmppath('dev.test'));
$parser->replace('_sidebar.tpl.php', $parser->template($_sidebar, 'in\\sidebar'));
$parsed = $parser->template($base, 'in\\base');
return assert::equals(
    $parser->extend($parsed, 'in\\base'),
    <<<'EXPECT'
<?php
// NAMESPACE:
namespace in\base;
?><!DOCTYPE html>
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


#: Test Import Exception
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#: Use Error
#: Expect Exception mysli\tplp\exception\parser 10
$parser = new parser(fs::tmppath('dev.test'));
$parser->extend( $parser->template($base) );
