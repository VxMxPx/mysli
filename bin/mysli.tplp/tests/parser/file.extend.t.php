<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;
use mysli\toolkit\fs\fs;
use mysli\toolkit\fs\file;

$file = <<<'FILE'
::extend _layout set contents
<p>Contents</p>
FILE;
file::write(fs::tmppath('dev.test/~file.tpl.html'), $file);

$file = <<<'FILE'
<html>
<body>
    ::print contents
</body>
</html>
FILE;
file::write(fs::tmppath('dev.test/_layout.tpl.html'), $file);


#: After
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
file::remove(fs::tmppath('dev.test/~file.tpl.html'));
file::remove(fs::tmppath('dev.test/_layout.tpl.html'));
file::remove(fs::tmppath('dev.test/~file.tpl.php'));
file::remove(fs::tmppath('dev.test/_layout.tpl.php'));


#: Test Extend
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$parser = new parser(fs::tmppath('dev.test'));
file::write(fs::tmppath('dev.test/~file.tpl.php'), $parser->file('~file.tpl.html'));
file::write(fs::tmppath('dev.test/_layout.tpl.php'), $parser->file('_layout.tpl.html'));

return assert::equals(
    $parser->file('~file.tpl.php'),
    <<<'EXPECT'
<?php
// NAMESPACE:
namespace tplp\template\file;
?><html>
<body>
<p>Contents</p>
</body>
</html>
EXPECT
);

