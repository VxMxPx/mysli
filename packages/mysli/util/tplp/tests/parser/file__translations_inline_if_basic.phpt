--TEST--
Translations
--VIRTUAL (test.tplp)--
{name if name else 'Anonymous'}
{user[posts] if user[posts]|count}
{'Anonymous' if !name else name}
{'Anonymous' if !name and not user[name]}
{@ANONYMOUS if !name and not user[name]}
{@ANONYMOUS(count) variable[1], variable[2] if !name and not user[name]}
--FILE--
<?php
use mysli\util\tplp\parser;
print_r(parser::file('test.tplp', __DIR__));
?>
--EXPECT--
<?php
namespace tplp\generic\test;
?><?php echo ($name) ? $name : 'Anonymous'; ?>
<?php echo (count($user['posts'])) ? $user['posts'] : ''; ?>
<?php echo (!$name) ? 'Anonymous' : $name; ?>
<?php echo (!$name and !$user['name']) ? 'Anonymous' : ''; ?>
<?php echo (!$name and !$user['name']) ? $tplp_translator_service('ANONYMOUS') : ''; ?>
<?php echo (!$name and !$user['name']) ? $tplp_translator_service(['ANONYMOUS', $count], [$variable['1'], $variable['2']]) : ''; ?>
