<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\extender;
use mysli\toolkit\fs\fs;
use mysli\toolkit\fs\file;

file::write(fs::tmppath('dev.test/base.tpl.html'), <<<'FILE'
::use mysli.cm.blog -> blog
::use mysli.cm.page -> page
<div>
    {variable}
</div>
FILE
);
file::write(fs::tmppath('dev.test/error.tpl.html'), <<<'FILE'
::use mysli.cms.blog
::use mysli.cms.blog
<div>
    {variable}
</div>
::use mysli.my.blog
<div>
    {variable[2]}
</div>
FILE
);

#: After
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
file::remove(fs::tmppath('dev.test/base.tpl.html'));
file::remove(fs::tmppath('dev.test/error.tpl.html'));

#: Test Extend Set
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$extender = new extender(fs::tmppath('dev.test'));
$template = $extender->process('base');

return assert::equals(
    $template,
    <<<'EXPECT'
<?php
namespace tplp\template\base;
use mysli\cm\blog\__tplp as blog;
use mysli\cm\page\__tplp as page;
?><div>
    <?php echo $variable; ?>
</div>
EXPECT
);

#: Test Use Exception
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#: Use Error
#: Expect Exception mysli\tplp\exception\extender 10
$extender = new extender(fs::tmppath('dev.test'));
$extender->process('error');
