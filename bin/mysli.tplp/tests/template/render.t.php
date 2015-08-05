<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\template;
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
file::remove(file::find(fs::tmppath('dev.test'), '*.html|*.php'));

#: Test Basic
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$template = new template(fs::tmppath('dev.test'));
return assert::equals(
    $template->render('~file', [ 'hello' => 'HELLO!!' ]),
    <<<'EXPECT'
<html>
<body>
    HELLO!!</body>
</html>
EXPECT
);
