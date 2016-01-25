<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\extender;
use mysli\toolkit\fs\fs;
use mysli\toolkit\fs\file;

file::write(fs::tmppath('dev.test/base.tpl.html'),  <<<'TEST'
<!DOCTYPE html>
<html>
<head>
    <title>{title}</title>
</head>
<body>
    ::import _sidebar
</body>
</html>
TEST
);

file::write(fs::tmppath('dev.test/_sidebar.tpl.html'), <<<'_SIDEBAR'
<div class="sidebar">
    <p>Hello world!</p>
</div>
_SIDEBAR
);

file::write(fs::tmppath('dev.test/error.tpl.html'), <<<'TEST'
<!DOCTYPE html>
<html>
<head>
    <title>{title}</title>
</head>
<body>
    ::import _non_existant_file
</body>
</html>
TEST
);

#: After
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
file::remove(fs::tmppath('dev.test/base.tpl.html'));
file::remove(fs::tmppath('dev.test/_sidebar.tpl.html'));
file::remove(fs::tmppath('dev.test/error.tpl.html'));

#: Test Import
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
#: Expect Exception mysli\tplp\exception\extender 10
$extender = new extender(fs::tmppath('dev.test'));
$extender->process('error');
