--TEST--
Control structure.
--VIRTUAL (test.tplp)--
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
--FILE--
<?php
use mysli\util\tplp\parser;
print_r(parser::file('test.tplp', __DIR__));
?>
--EXPECT--
<?php
namespace tplp\generic\test;
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
