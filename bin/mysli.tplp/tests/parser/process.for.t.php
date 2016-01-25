<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;

#: Test For
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$template = <<<'TEMPLATE'
<html>
<body>
    ::for user in users
    ::/for

    ::for id,user in users
    ::/for

    ::for user in collection[users]
    ::/for

    ::for user in collection->users
    ::/for

    ::for user in users|slice:0,10
    ::/for
</body>
</html>
TEMPLATE;
$parser = new parser();
return assert::equals(
    $parser->process($template),
    <<<'EXPECT'
<html>
<body>
    <?php foreach ($users as $user): ?>
    <?php endforeach; ?>
    <?php foreach ($users as $id => $user): ?>
    <?php endforeach; ?>
    <?php foreach ($collection['users'] as $user): ?>
    <?php endforeach; ?>
    <?php foreach ($collection->users as $user): ?>
    <?php endforeach; ?>
    <?php foreach (( is_array($users) ? array_slice($users, 0, 10) : substr($users, 0, 10) ) as $user): ?>
    <?php endforeach; ?>
</body>
</html>
EXPECT
);
