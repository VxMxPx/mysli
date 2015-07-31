<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;
use mysli\toolkit\fs\fs;
use mysli\toolkit\fs\file;


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
file::write(fs::tmppath('dev.test/~test.tpl.html'), $base);

$_sidebar = <<<'_SIDEBAR'
<div class="sidebar">
    <p>Hello world!</p>
</div>
_SIDEBAR;
file::write(fs::tmppath('dev.test/_sidebar.tpl.html'), $_sidebar);


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
file::write(fs::tmppath('dev.test/~test.tpl.html'), $base);


#: After
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
file::remove(fs::tmppath('dev.test/~test.tpl.html'));
file::remove(fs::tmppath('dev.test/_sidebar.tpl.html'));


#: Test Import
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#: Use Import
$processed = parser::file('~test.tpl.html', fs::tmppath('dev.test'));
return assert::equals(
    $processed,
    <<<'EXPECT'
<?php
namespace tplp\template\test;
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
$processed = parser::file('~test.tpl.html', fs::tmppath('dev.test'));
