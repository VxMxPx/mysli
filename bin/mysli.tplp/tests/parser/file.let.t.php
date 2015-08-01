<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;
use mysli\toolkit\fs\fs;

#: Define File
$file = <<<'FILE'
::let var_name = 'Marko'

::let var_multiline do
    This will act exactly the same
    as it would in HTML. This text will be displayed in single line.
    That's the thing...
    That\'s the escaped thing...
::/let

::let var_tags set implode(, ) do
    one
    two
    three
::/let

::let var_array set array do
    Item one
    Item two
    Item three
::/let

::let var_cities set dictionary(:) do
    Dublin : Ireland
    Moscow : Russia
    Ljubljana : Slovenia
    Paris : France
    Kyiv : Ukraine
::/let
FILE;


#: Test Let
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#: Use File
$processed = parser::file(
    '~test.tpl.html',
    fs::tmppath('dev.test'),
    [ '~test' => $file ]
);
return assert::equals(
    $processed,
    <<<'EXPECT'
<?php
namespace tplp\template\test;
?><?php $var_name = 'Marko'; ?>
<?php $var_multiline = 'This will act exactly the same as it would in HTML. This text will be displayed in single line. Tha\'s the thing... That\'s the escaped thing...'; ?>
<?php $var_tags = 'one, two, three'; ?>
<?php $var_array = unserialize('a:3:{i:0;s:8:"Item one";i:1;s:8:"Item two";i:2;s:10:"Item three";}'); ?>
<?php $var_cities = unserialize('a:5:{s:6:"Dublin";s:7:"Ireland";s:6:"Moscow";s:6:"Russia";s:9:"Ljubljana";s:8:"Slovenia";s:5:"Paris";s:6:"France";s:4:"Kyiv";s:7:"Ukraine";}'); ?>
EXPECT
);
