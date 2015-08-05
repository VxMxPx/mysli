<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;
use mysli\toolkit\fs\fs;

#: Define File
$file = <<<'FILE'
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
FILE;

#: Test For
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#: Use File
$parser = new parser(fs::tmppath('dev.test'));
return assert::equals(
    $parser->template($file),
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