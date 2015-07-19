<?php

#: Before
use mysli\toolkit\type\arr;

#: Test Basic
$array = [4];
arr::prepend($array, 5);
return assert::equals( $array, [5, 4] );
