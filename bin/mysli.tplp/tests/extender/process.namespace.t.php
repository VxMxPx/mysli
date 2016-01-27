<?php

#: Before
use mysli\tplp\extender;
use mysli\toolkit\fs\fs;

#: Define Files
$files = [
    fs::tmppath('dev.test/base') => <<<'BASE'
<div>
    Some content here...
</div>
BASE
];

#: Test Import
#: Use Files
$extender = new extender(fs::tmppath('dev.test'));
foreach ($files as $id => $template) $extender->set_cache($id, $template);
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
