<?php


# ------------------------------------------------------------------------------
#: Test Resolve Use, Specific
autoloader::__t_reset();
autoloader::resolve_use('vend1\\pkg1\\class', 'vend2.pkg2.another');
return assert::equals(
    autoloader::__t_dump(),
    [ 'vend1\pkg1\another' => 'vend2\pkg2\another' ]);

# ------------------------------------------------------------------------------
#: Test Resolve Use, Curly
autoloader::__t_reset();
autoloader::resolve_use('vend1\\pkg1\\class', 'vend2.pkg2.{ another }');
return assert::equals(
    autoloader::__t_dump(),
    [ 'vend1\pkg1\another' => 'vend2\pkg2\another' ]);

# ------------------------------------------------------------------------------
#: Test Resolve Use, Multiple Inline
autoloader::__t_reset();
autoloader::resolve_use(
    'vend1\\pkg1\\class',
    'vend2.pkg2.{ another, another2, another3, another4 }'
);
return assert::equals(
    autoloader::__t_dump(),
    [
        'vend1\pkg1\another'  => 'vend2\pkg2\another',
        'vend1\pkg1\another2' => 'vend2\pkg2\another2',
        'vend1\pkg1\another3' => 'vend2\pkg2\another3',
        'vend1\pkg1\another4' => 'vend2\pkg2\another4',
    ]);

# ------------------------------------------------------------------------------
#: Test Resolve Use, Multiple Lined
autoloader::__t_reset();
autoloader::resolve_use(
    'vend1\\pkg1\\class',
    'vend2.pkg2.{
        another
        another2
        another3
        another4
    }'
);
return assert::equals(
    autoloader::__t_dump(),
    [
        'vend1\pkg1\another'  => 'vend2\pkg2\another',
        'vend1\pkg1\another2' => 'vend2\pkg2\another2',
        'vend1\pkg1\another3' => 'vend2\pkg2\another3',
        'vend1\pkg1\another4' => 'vend2\pkg2\another4',
    ]);

# ------------------------------------------------------------------------------
#: Test Resolve Use, Multiple Lined, Irregular
autoloader::__t_reset();
autoloader::resolve_use(
    'vend1\\pkg1\\class',
    'vend2.pkg2.{ another, another2
        another3
        another4 }'
);
return assert::equals(
    autoloader::__t_dump(),
    [
        'vend1\pkg1\another'  => 'vend2\pkg2\another',
        'vend1\pkg1\another2' => 'vend2\pkg2\another2',
        'vend1\pkg1\another3' => 'vend2\pkg2\another3',
        'vend1\pkg1\another4' => 'vend2\pkg2\another4',
    ]);

# ------------------------------------------------------------------------------
#: Test Resolve Use, Multiple Various, All Inline
autoloader::__t_reset();
autoloader::resolve_use(
    'vend1\\pkg1\\class',
    'vend2.pkg2.{ another, another2}, vend2.pkg3.{ another3, another4 }'
);
return assert::equals(
    autoloader::__t_dump(),
    [
        'vend1\pkg1\another'  => 'vend2\pkg2\another',
        'vend1\pkg1\another2' => 'vend2\pkg2\another2',
        'vend1\pkg1\another3' => 'vend2\pkg3\another3',
        'vend1\pkg1\another4' => 'vend2\pkg3\another4',
    ]);

# ------------------------------------------------------------------------------
#: Test Resolve Use, Named
autoloader::__t_reset();
autoloader::resolve_use(
    'vend1\\pkg1\\class',
    'vend2.pkg2.{ another -> anth }'
);
return assert::equals(
    autoloader::__t_dump(),
    [
        'vend1\pkg1\anth' => 'vend2\pkg2\another',
    ]);

# ------------------------------------------------------------------------------
#: Test Resolve Use, Self
autoloader::__t_reset();
autoloader::resolve_use(
    'mysli\\toolkit\\class',
    '.{ another -> anth }'
);
return assert::equals(
    autoloader::__t_dump(),
    [
        'mysli\toolkit\anth' => 'mysli\toolkit\another'
    ]);
