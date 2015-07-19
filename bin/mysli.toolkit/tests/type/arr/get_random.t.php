<?php

#: Before
use mysli\toolkit\type\arr;

#: Define Data
$data = ['a', 'b', 'c', 'd'];

#: Test Count
#: Use Data
#: Expect Integer 1
return count(arr::get_random($data));

#: Test In Array
#: Use Data
#: Expect True
return in_array(arr::get_random($data), $data);

#: Test Random, All Elements Are Not Equal
#: Use Data
#: Expect True
$a = arr::get_random($data);
$b = arr::get_random($data);
$c = arr::get_random($data);
$d = arr::get_random($data);

return !($a === $b && $b === $c && $c === $d);
