<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;
use mysli\toolkit\fs\fs;
use mysli\toolkit\fs\file;

$base = <<<'TEST'
::extend _layout set content
<div>
    Some content here...
</div>
TEST;
file::write(fs::tmppath('dev.test/~test.tpl.html'), $base);

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
file::write(fs::tmppath('dev.test/_layout.tpl.html'), $_layout);


#: After
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
file::remove(fs::tmppath('dev.test/~test.tpl.html'));
file::remove(fs::tmppath('dev.test/_layout.tpl.html'));


#: Test Extend
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
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
<div>
    Some content here...
</div>
</body>
</html>
EXPECT
);
