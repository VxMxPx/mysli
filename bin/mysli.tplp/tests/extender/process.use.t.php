<?php

#: Before
use mysli\tplp\extender;
use mysli\toolkit\fs\fs;

#: Define Files
$files = [
    fs::tmppath('dev.test/base') => <<<'BASE'
::use mysli.cm.blog -> blog
::use mysli.cm.page -> page
<div>
    Hello!
</div>
BASE
,
    fs::tmppath('dev.test/error') => <<<'ERROR'
::use mysli.cms.blog
::use mysli.cms.blog
<div>
    Hello!
</div>
::use mysli.my.blog
<div>
    Hello!
</div>
ERROR
];

#: Test Extend Set
#: Use Files
$extender = new extender(fs::tmppath('dev.test'));
foreach ($files as $id => $template) $extender->set_cache($id, $template);
$template = $extender->process('base');

return assert::equals(
    $template,
    <<<'EXPECT'
<?php
namespace tplp\template\base;
use mysli\cm\blog\__tplp as blog;
use mysli\cm\page\__tplp as page;
?><div>
    Hello!
</div>
EXPECT
);

#: Test Use Exception
#: Use Files
#: Expect Exception mysli\tplp\exception\extender 10
$extender = new extender(fs::tmppath('dev.test'));
foreach ($files as $id => $template) $extender->set_cache($id, $template);
$extender->process('error');
