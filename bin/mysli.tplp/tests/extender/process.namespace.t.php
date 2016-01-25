<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\extender;
use mysli\toolkit\fs\fs;
use mysli\toolkit\fs\file;

file::write(fs::tmppath('dev.test/base.tpl.html'), <<<'TEST'
<div>
    Some content here...
</div>
TEST
);

#: After
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
file::remove(fs::tmppath('dev.test/base.tpl.html'));

#: Test Import
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$extender = new extender(fs::tmppath('dev.test'));
$template = $extender->process('base');

return assert::equals(
    $template,
    <<<'EXPECT'
<?php
namespace tplp\template\base;
?><div>
    Some content here...
</div>
EXPECT
);
