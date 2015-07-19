<?php

#: Before
use mysli\toolkit\type\arr;

#: Test Basic
$array = [4, 6];
arr::insert($array, 5, 1);
return assert::equals($array, [4, 5, 6]);

#: Test Too Far Out
$array = [4, 6];
arr::insert($array, 5, 10);
return assert::equals($array, [4, 6, 5]);

#: Test Negative
$array = [4, 5, 6, 7, 9];
arr::insert($array, 8, -1);
return assert::equals($array, [4, 5, 6, 7, 8, 9]);
