<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;

#: Test Function Namespaced
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$template = <<<'TEMPLATE'
<html>
<body>
    ::use mysli.assets
    {'css/main.css'|assets.tags:'template:default'}
    {|assets.tags:variable.property}
</body>
</html>
TEMPLATE;
$parser = new parser();
return assert::equals(
    $parser->process($template),
    <<<'EXPECT'
<html>
<body>
    ::use mysli.assets
    <?php echo assets::tags('css/main.css', 'template:default'); ?>
    <?php echo assets::tags($variable->property); ?>
</body>
</html>
EXPECT
);
