<?php

#: Before
use mysli\toolkit\type\arr;

#: Test Count Values, Case Sensitive
$values = ['Maribor', 'koper', 'Ptuj', 'Maribor', 'Koper'];
return assert::equals(
    arr::count_values($values),
    ['Maribor' => 2, 'koper' => 1, 'Ptuj' => 1, 'Koper' => 1]
);

#: Test Count Values, Case Insensitive
$values = ['Maribor', 'koper', 'Ptuj', 'Maribor', 'Koper'];
return assert::equals(
    arr::count_values($values, false),
    ['maribor' => 2, 'koper' => 2, 'ptuj' => 1]
);

#: Test Count Value Exception, MultiDimensional Array
#: Expect Exception mysli\toolkit\exception\validate
arr::count_values([[], []], false);

#: Test Numeric Values
$values = ['Domžale', 30, 'Škofja Loka', 30, 'Šoštanj', 'Domžale', 'škofja Loka', 30];
return assert::equals(
    arr::count_values($values, false),
    ['domžale' => 2, '30' => 3, 'škofja loka' => 2, 'šoštanj' => 1]
);
