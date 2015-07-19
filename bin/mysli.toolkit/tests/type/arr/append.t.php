<?php

#: Before
use mysli\toolkit\type\arr;

#: Test Append
$array = [4];
arr::append($array, 5);
return assert::equals($array, [4, 5]);
