<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;
use mysli\toolkit\fs\fs;
use mysli\toolkit\fs\file;

$file = <<<'FILE'
<html>
<body>
    ::import _sidebar
</body>
</html>
FILE;
file::write(fs::tmppath('dev.test/~file.tpl.html'), $file);

$file = <<<'FILE'
<div class="sidebar">
    <p>Contents</p>
</div>
FILE;
file::write(fs::tmppath('dev.test/_sidebar.tpl.html'), $file);


#: After
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
file::remove(fs::tmppath('dev.test/~file.tpl.html'));
file::remove(fs::tmppath('dev.test/_sidebar.tpl.html'));
file::remove(fs::tmppath('dev.test/~file.tpl.php'));
file::remove(fs::tmppath('dev.test/_sidebar.tpl.php'));


#: Test Import
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$parser = new parser(fs::tmppath('dev.test'));
file::write(fs::tmppath('dev.test/~file.tpl.php'), $parser->file('~file.tpl.html'));
file::write(fs::tmppath('dev.test/_sidebar.tpl.php'), $parser->file('_sidebar.tpl.html'));

return assert::equals(
    $parser->file('~file.tpl.php'),
    <<<'EXPECT'
<?php
// NAMESPACE:
namespace tplp\template\file;
?><html>
<body>
<div class="sidebar">
    <p>Contents</p>
</div>
</body>
</html>
EXPECT
);

