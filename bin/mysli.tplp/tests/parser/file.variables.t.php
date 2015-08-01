<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;
use mysli\toolkit\fs\fs;

#: Define File
$file = <<<'FILE'
<html>
<body>
    {variable}
    {variable[key]}
    {variable[key][subkey]}
    {variable[key][subkey][subsubkey]}
    {variable->property}
    {''}
    {12}
    {-12}
    {true}
    {false}
    {null}
    {variable1} {variable2}

    {((variable))}
</body>
</html>
FILE;

#: Test Translation
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
?><html>
<body>
    <?php echo $variable; ?>
    <?php echo $variable['key']; ?>
    <?php echo $variable['key']['subkey']; ?>
    <?php echo $variable['key']['subkey']['subsubkey']; ?>
    <?php echo $variable->property; ?>
    <?php echo ''; ?>
    <?php echo 12; ?>
    <?php echo -12; ?>
    <?php echo true; ?>
    <?php echo false; ?>
    <?php echo null; ?>
    <?php echo $variable1; ?> <?php echo $variable2; ?>
    <?php $variable; ?>
</body>
</html>
EXPECT
);
