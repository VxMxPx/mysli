<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\extender;
use mysli\toolkit\fs\fs;
use mysli\toolkit\fs\file;

file::write(fs::tmppath('dev.test/base.tpl.html'), <<<'TEST'
::extend _layout set content do
    ::set styles
    <link rel="stylesheet" type="text/css" href="main.css">
    <link rel="stylesheet" type="text/css" href="mobile.css">
    ::/set
::/extend
<div>
    Some content here...
</div>
TEST
);

file::write(fs::tmppath('dev.test/_layout.tpl.html'), <<<'_LAYOUT'
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
_LAYOUT
);

#: After
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
file::remove(fs::tmppath('dev.test/base.tpl.html'));
file::remove(fs::tmppath('dev.test/_layout.tpl.html'));

#: Test Extend Do
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$extender = new extender(fs::tmppath('dev.test'));
$template = $extender->process('base');

return assert::equals(
    $template,
    <<<'EXPECT'
<?php
namespace tplp\template\base;
?><!DOCTYPE html>
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
