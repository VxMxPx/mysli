<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;

#: Test Translation
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$template = <<<'TEMPLATE'
<html>
<body>
    {variable}
    {variable[key]}
    {variable[key][subkey]}
    {variable[key][subkey][subsubkey]}
    {variable->property}
    {variable[key].property}
    {variable.property[key]}
    {variable[key.sub].property[key->sub]}
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
TEMPLATE;
$parser = new parser();
return assert::equals(
    $parser->process($template),
    <<<'EXPECT'
<html>
<body>
    <?php echo $variable; ?>
    <?php echo $variable['key']; ?>
    <?php echo $variable['key']['subkey']; ?>
    <?php echo $variable['key']['subkey']['subsubkey']; ?>
    <?php echo $variable->property; ?>
    <?php echo $variable['key']->property; ?>
    <?php echo $variable->property['key']; ?>
    <?php echo $variable['key.sub']->property['key->sub']; ?>
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
