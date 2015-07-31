<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;
use mysli\toolkit\fs\fs;
use mysli\toolkit\fs\file;

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
file::write(fs::tmppath('dev.test/~test.tpl.html'), $file);


#: After
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
file::remove(fs::tmppath('dev.test/~test.tpl.html'));


#: Test For
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$processed = parser::file('~test.tpl.html', fs::tmppath('dev.test'));
return assert::equals(
    $processed,
    <<<'EXPECT'
<?php
namespace tplp\template\test;
?><html>
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