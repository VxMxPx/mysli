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
$processed = parser::file(
    '~test.tpl.html',
    fs::tmppath('dev.test'),
    [ '~test' => $base, '_layout' => $_layout ]
);
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
