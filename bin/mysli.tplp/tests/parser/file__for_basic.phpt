--TEST--
--VIRTUAL (test.tplp)--
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
--FILE--
<?php
use mysli\util\tplp\parser;
print_r(parser::file('test.tplp', __DIR__));
?>
--EXPECT--
<?php
namespace tplp\generic\test;
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
