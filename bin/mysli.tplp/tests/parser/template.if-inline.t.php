<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;
use mysli\toolkit\fs\fs;

#: Define File
$file = <<<'FILE'
{name if name else 'Anonymous'}
{user[posts] if user[posts]|count}
{'Anonymous' if !name else name}
{'Anonymous' if !name and not user[name]}
{@ANONYMOUS if !name and not user[name]}
{@ANONYMOUS(count) variable[1], variable[2] if !name and not user[name]}
FILE;

#: Test If Inline
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#: Use File
$parser = new parser(fs::tmppath('dev.test'));
return assert::equals(
    $parser->template($file),
    <<<'EXPECT'
<?php echo ($name) ? $name : 'Anonymous'; ?>
<?php echo (count($user['posts'])) ? $user['posts'] : ''; ?>
<?php echo (!$name) ? 'Anonymous' : $name; ?>
<?php echo (!$name and !$user['name']) ? 'Anonymous' : ''; ?>
<?php echo (!$name and !$user['name']) ? $tplp_func_translator_service('ANONYMOUS') : ''; ?>
<?php echo (!$name and !$user['name']) ? $tplp_func_translator_service(['ANONYMOUS', $count], [$variable['1'], $variable['2']]) : ''; ?>
EXPECT
);
