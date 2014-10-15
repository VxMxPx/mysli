--TEST--
For
--FILE--
<?php
use mysli\util\tplp\parser;

$input = <<<INPUT
::for user in users set pos
::/for

::for user in users|slice:0,10 set p
::/for
INPUT;

print_r(parser::process($input));
?>
--EXPECT--
<?php
$pos = [];
$tplp_var_for_pos = $users;
$pos['count'] = count($tplp_var_for_pos);
$pos['current'] = 0;
foreach ($tplp_var_for_pos as $user):
  $pos['current']++;
  $pos['first'] = ($pos['current'] === 1);
  $pos['last'] = ($pos['current'] === $pos['count']);
  $pos['odd'] = !!($pos['current'] % 2);
  $pos['even'] = !($pos['current'] % 2);
?>
<?php endforeach; ?>
<?php
$p = [];
$tplp_var_for_p = ( is_array($users) ? array_slice($users, 0, 10) : substr($users, 0, 10) );
$p['count'] = count($tplp_var_for_p);
$p['current'] = 0;
foreach ($tplp_var_for_p as $user):
  $p['current']++;
  $p['first'] = ($p['current'] === 1);
  $p['last'] = ($p['current'] === $p['count']);
  $p['odd'] = !!($p['current'] % 2);
  $p['even'] = !($p['current'] % 2);
?>
<?php endforeach; ?>
