<?php

#: Before
use mysli\toolkit\type\arr;

#: Test Basic
$data = [ '  name ', ' capital    ', ' area  ', ' population ', 'hdi        ' ];
return assert::equals(
    arr::trim_values($data),
    [ 'name', 'capital', 'area', 'population', 'hdi' ]
);
