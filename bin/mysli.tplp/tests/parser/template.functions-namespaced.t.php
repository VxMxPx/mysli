<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;
use mysli\toolkit\fs\fs;

#: Define File
$file = <<<'FILE'
<html>
<body>
    ::use mysli.assets
    {'css/main.css'|assets.tags:'template:default'}
    {|assets.tags:variable.property}
</body>
</html>
FILE;

#: Test Function Namespaced
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#: Use File
$parser = new parser(fs::tmppath('dev.test'));
return assert::equals(
    $parser->template($file),
    <<<'EXPECT'
<?php
use mysli\assets\__tplp as assets;
?><html>
<body>
    <?php echo assets::tags('css/main.css', 'template:default'); ?>
    <?php echo assets::tags($variable->property); ?>
</body>
</html>
EXPECT
);
