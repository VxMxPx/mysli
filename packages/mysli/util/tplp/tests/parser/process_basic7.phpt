--TEST--
Inserts
--DESCRIPTION--
Inserts are are ignored, when `process` is called. A `file` method must be used
to properly process imports.
--FILE--
<?php
use mysli\util\tplp\parser;

$input = <<<INPUT
::use mysli/cms/blog
::use mysli/cms/page

::extend ./layout set content
::extend mysli/blog/master set content
::extend ./layout set content do
    ::set navigation
        ::if somevar
            {somevar}
        ::/if
    ::/set
::/extend

::import ./sidebar
::import ./sidebar do
    ::set navigation
        ::if nav
            {nav}
        ::/if
    ::/set
::/import

::import sidebar from ./modules do
    ::set navigation
        ::if nav
            {nav}
        ::/if
    ::/set
::/import
INPUT;

print_r(parser::process($input));
?>
--EXPECT--
::use mysli/cms/blog
::use mysli/cms/page
::extend ./layout set content
::extend mysli/blog/master set content
::extend ./layout set content do
    ::set navigation
        <?php if ($somevar): ?>
            <?php echo $somevar; ?>
        <?php endif; ?>
    ::/set
::/extend
::import ./sidebar
::import ./sidebar do
    ::set navigation
        <?php if ($nav): ?>
            <?php echo $nav; ?>
        <?php endif; ?>
    ::/set
::/import
::import sidebar from ./modules do
    ::set navigation
        <?php if ($nav): ?>
            <?php echo $nav; ?>
        <?php endif; ?>
    ::/set
::/import
