<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;

#: Test Variables Basic
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$template = <<<'TEMPLATE'
<html>
<body>
    {variable}
    {variable1} {variable2}
    {; variable }
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
    <?php echo $variable1; ?> <?php echo $variable2; ?>
    <?php $variable; ?>
</body>
</html>
EXPECT
);

#: Test Variables Complex
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$template = <<<'TEMPLATE'
<html>
<body>
    {variable['key']}
    {variable['key']['subkey']}
    {variable['key']['subkey']['subsubkey']}
    {variable->property}
    {variable['key']->property}
    {variable->property['key']}
    {variable['key->sub']->property['key->sub']}
    {variable[var][var2]}
</body>
</html>
TEMPLATE;
$parser = new parser();
return assert::equals(
    $parser->process($template),
    <<<'EXPECT'
<html>
<body>
    <?php echo $variable['key']; ?>
    <?php echo $variable['key']['subkey']; ?>
    <?php echo $variable['key']['subkey']['subsubkey']; ?>
    <?php echo $variable->property; ?>
    <?php echo $variable['key']->property; ?>
    <?php echo $variable->property['key']; ?>
    <?php echo $variable['key->sub']->property['key->sub']; ?>
    <?php echo $variable[$var][$var2]; ?>
</body>
</html>
EXPECT
);

#: Test Variables Values
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$template = <<<'TEMPLATE'
<html>
<body>
    {''}
    {12}
    {-12}
    {true}
    {false}
    {null}
</body>
</html>
TEMPLATE;
$parser = new parser();
return assert::equals(
    $parser->process($template),
    <<<'EXPECT'
<html>
<body>
    <?php echo ''; ?>
    <?php echo 12; ?>
    <?php echo -12; ?>
    <?php echo true; ?>
    <?php echo false; ?>
    <?php echo null; ?>
</body>
</html>
EXPECT
);

#: Test Variables Math
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$template = <<<'TEMPLATE'
<html>
<body>
    {number++}
    {number--}

    {number+12}
    {number-2}
    {number*45}
    {number/34}

    {name|funct:'12+12'}
    { product->price + product->tax - user['discount'] }
</body>
</html>
TEMPLATE;
$parser = new parser();
return assert::equals(
    $parser->process($template),
    <<<'EXPECT'
<html>
<body>
    <?php echo $number++; ?>
    <?php echo $number--; ?>
    <?php echo $number+12; ?>
    <?php echo $number-2; ?>
    <?php echo $number*45; ?>
    <?php echo $number/34; ?>
    <?php echo $tplp_func_funct($name, '12+12'); ?>
    <?php echo $product->price+$product->tax-$user['discount']; ?>
</body>
</html>
EXPECT
);

#: Test Variables Multiple Added
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$template = <<<'TEMPLATE'
<html>
<body>
    {name.lastname}
    {name.' '.middlename.' '.lastname}
    {; name.' '.lastname}
</body>
</html>
TEMPLATE;
$parser = new parser();
return assert::equals(
    $parser->process($template),
    <<<'EXPECT'
<html>
<body>
    <?php echo $name.$lastname; ?>
    <?php echo $name.' '.$middlename.' '.$lastname; ?>
    <?php $name.' '.$lastname; ?>
</body>
</html>
EXPECT
);

#: Test Variables Add Mixed
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$template = <<<'TEMPLATE'
<html>
<body>
    { price + tax - discount + curency }
</body>
</html>
TEMPLATE;
$parser = new parser();
return assert::equals(
    $parser->process($template),
    <<<'EXPECT'
<html>
<body>
    <?php echo $price+$tax-$discount+$curency; ?>
</body>
</html>
EXPECT
);

#: Test Variables Assign
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$template = <<<'TEMPLATE'
<html>
<body>
    {; sum = price + tax - discount + curency }
    {; greeting = 'Hi there!' }
    {; number = -12 }
    {year = post['year']}
    {;year = 0}
    {;name = ''}
</body>
</html>
TEMPLATE;
$parser = new parser();
return assert::equals(
    $parser->process($template),
    <<<'EXPECT'
<html>
<body>
    <?php $sum=$price+$tax-$discount+$curency; ?>
    <?php $greeting='Hi there!'; ?>
    <?php $number=-12; ?>
    <?php echo $year=$post['year']; ?>
    <?php $year=0; ?>
    <?php $name=''; ?>
</body>
</html>
EXPECT
);

#: Test Variables Assign + Function Call
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$template = <<<'TEMPLATE'
<html>
<body>
    {; name.' '.lastname|ucwords}
    {; one+two+three+four|lower}
    {; item.'|'.item|ucwords}
</body>
</html>
TEMPLATE;
$parser = new parser();
return assert::equals(
    $parser->process($template),
    <<<'EXPECT'
<html>
<body>
    <?php ucwords($name.' '.$lastname); ?>
    <?php strtolower($one+$two+$three+$four); ?>
    <?php ucwords($item.'|'.$item); ?>
</body>
</html>
EXPECT
);
