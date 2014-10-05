--TEST--
Control structure.
--FILE--
<?php
use mysli\util\tplp\parser;

$input = <<<INPUT
<html>
<body>
    ::if variable > 0
        Above zero.
    ::elif variable < 0
        Bellow zero.
    ::else
        Zero.
    ::/if

    ::if collection[users] &gt; 10
    ::/if

    ::if collection[users] &gt;= 10
    ::/if

    ::if variable AND variable > 10 AND variable &lt; 50
    ::/if

    ::if users|count > 10
    ::/if

    ::if users|slice:0,20|count > 10
    ::/if
</body>
</html>
INPUT;

print_r(parser::process($input));
?>
--EXPECT--
<html>
<body>
    <?php if ($variable > 0): ?>
        Above zero.
    <?php elseif ($variable < 0): ?>
        Bellow zero.
    <?php else: ?>
        Zero.
    <?php endif; ?>
    <?php if ($collection['users'] > 10): ?>
    <?php endif; ?>
    <?php if ($collection['users'] >= 10): ?>
    <?php endif; ?>
    <?php if ($variable AND $variable > 10 AND $variable < 50): ?>
    <?php endif; ?>
    <?php if (count($users) > 10): ?>
    <?php endif; ?>
    <?php if (count(( is_array($users) ? array_slice($users, 0, 20) : substr($users, 0, 20) )) > 10): ?>
    <?php endif; ?>
</body>
</html>
