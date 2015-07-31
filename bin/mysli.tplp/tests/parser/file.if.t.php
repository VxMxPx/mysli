<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;
use mysli\toolkit\fs\fs;
use mysli\toolkit\fs\file;


#: Define Basic
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$file = <<<'FILE'
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
FILE;
file::write(fs::tmppath('dev.test/~test.tpl.html'), $file);


#: Define Conditions
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$file = <<<'FILE'
::if not posts|count and not comments|count
::/if

::if one and (two or (three and four or (five and six))) and seven
::/if

::if one and ( two or ( three and four or ( five and six ) ) ) and seven
::/if

::if one and (two or (three and four or (five and six))) and not seven
::/if

::if !one and !(two or (!three and !four or !(five and six)))
::/if

::if ! one and ! (two or (! three and ! four or ! (five and six)))
::/if


::if ! one and ! ( two or ( ! three and ! four or ! ( five and six ) ) )
::/if

::if not one and not (two or (not three and not four or not (five and six)))
::/if
FILE;
file::write(fs::tmppath('dev.test/~test.tpl.html'), $file);


#: After
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
file::remove(fs::tmppath('dev.test/~test.tpl.html'));


#: Test If Basic
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#: Use Basic
$processed = parser::file('~test.tpl.html', fs::tmppath('dev.test'));
return assert::equals(
    $processed,
    <<<'EXPECT'
<?php
namespace tplp\template\test;
?><html>
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
EXPECT
);


#: Test If Multiple Conditions
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#: Use Conditions
$processed = parser::file('~test.tpl.html', fs::tmppath('dev.test'));
return assert::equals(
    $processed,
    <<<'EXPECT'
<?php
namespace tplp\template\test;
?><?php if (!count($posts) and !count($comments)): ?>
<?php endif; ?>
<?php if ($one and ($two or ($three and $four or ($five and $six))) and $seven): ?>
<?php endif; ?>
<?php if ($one and ($two or ($three and $four or ($five and $six))) and $seven): ?>
<?php endif; ?>
<?php if ($one and ($two or ($three and $four or ($five and $six))) and !$seven): ?>
<?php endif; ?>
<?php if (!$one and !($two or (!$three and !$four or !($five and $six)))): ?>
<?php endif; ?>
<?php if (!$one and !($two or (!$three and !$four or !($five and $six)))): ?>
<?php endif; ?>
<?php if (!$one and !($two or (!$three and !$four or !($five and $six)))): ?>
<?php endif; ?>
<?php if (!$one and !($two or (!$three and !$four or !($five and $six)))): ?>
<?php endif; ?>
EXPECT
);
