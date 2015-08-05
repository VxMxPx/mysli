<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;
use mysli\toolkit\fs\fs;
use mysli\toolkit\fs\file;

$file = <<<'FILE'
<html>
<body>
    {hello}
</body>
</html>
FILE;
file::write(fs::tmppath('dev.test/~file.tpl.html'), $file);

#: After
file::remove(fs::tmppath('dev.test/~file.tpl.html'));

#: Test File
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$parser = new parser(fs::tmppath('dev.test'));
return assert::equals(
    $parser->file('~file.tpl.html'),
    <<<'EXPECT'
<html>
<body>
    <?php echo $hello; ?>
</body>
</html>
EXPECT
);
