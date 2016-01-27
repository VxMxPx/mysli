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
file::write(fs::tmppath('dev.test/base.tpl.html'), $file);

#: After
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
file::remove(fs::tmppath('dev.test/base.tpl.html'));

#: Test Basic
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$template = new template(fs::tmppath('dev.test'));
$rendered = $template->render('base', [ 'hello' => 'HELLO!!' ], true);
return assert::equals(
    $rendered,
    <<<'EXPECT'
<html>
<body>
    HELLO!!</body>
</html>
EXPECT
);
