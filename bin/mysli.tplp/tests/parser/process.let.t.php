<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;

#: Test Let
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$template = <<<'TEMPLATE'
::let var_name = 'Marko'

::let var_name = post['title']

::let var_multiline from
    This will act exactly the same
    as it would in HTML. This text will be displayed in single line.
    That's the thing...
    That\'s the escaped thing...
::/let

::let var_tags set implode(, ) from
    one
    two
    three
::/let

::let var_array set array from
    Item one
    Item two
    Item three
::/let

::let var_cities set dictionary(:) from
    Dublin : Ireland
    Moscow : Russia
    Ljubljana : Slovenia
    Paris : France
    Kyiv : Ukraine
::/let
TEMPLATE;
$parser = new parser();
return assert::equals(
    $parser->process($template),
    <<<'EXPECT'
<?php $var_name = 'Marko'; ?>
<?php $var_name = $post['title']; ?>
<?php $var_multiline = 'This will act exactly the same as it would in HTML. This text will be displayed in single line. Tha\'s the thing... That\'s the escaped thing...'; ?>
<?php $var_tags = 'one, two, three'; ?>
<?php $var_array = unserialize('a:3:{i:0;s:8:"Item one";i:1;s:8:"Item two";i:2;s:10:"Item three";}'); ?>
<?php $var_cities = unserialize('a:5:{s:6:"Dublin";s:7:"Ireland";s:6:"Moscow";s:6:"Russia";s:9:"Ljubljana";s:8:"Slovenia";s:5:"Paris";s:6:"France";s:4:"Kyiv";s:7:"Ukraine";}'); ?>
EXPECT
);
