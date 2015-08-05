<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;
use mysli\toolkit\fs\fs;
use mysli\toolkit\fs\file;

$file = <<<'FILE'
<html>
<body>
    ::import sidebar from _modules
    ::import footer from _modules
</body>
</html>
FILE;
file::write(fs::tmppath('dev.test/~file.tpl.html'), $file);

$file = <<<'FILE'
::module sidebar
<div class="sidebar">
    <p>Contents</p>
</div>
::/module
::module footer
<div class="footer">Aaaj!</div>
::/module
FILE;
file::write(fs::tmppath('dev.test/_modules.tpl.html'), $file);


#: After
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
file::remove(fs::tmppath('dev.test/~file.tpl.html'));
file::remove(fs::tmppath('dev.test/_modules.tpl.html'));
file::remove(fs::tmppath('dev.test/~file.tpl.php'));
file::remove(fs::tmppath('dev.test/_modules.tpl.php'));


#: Test Import
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$parser = new parser(fs::tmppath('dev.test'));
file::write(fs::tmppath('dev.test/~file.tpl.php'), $parser->file('~file.tpl.html'));
file::write(fs::tmppath('dev.test/_modules.tpl.php'), $parser->file('_modules.tpl.html'));

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
<div class="footer">Aaaj!</div>
</body>
</html>
EXPECT
);

