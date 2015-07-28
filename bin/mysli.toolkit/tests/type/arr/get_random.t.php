<?php

#: Before
use mysli\toolkit\type\arr;

#: Define Data
$data = [
    'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o',
    'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'
];

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
$previous = arr::get_random($data);
for ($i=0; $i < count($data); $i++)
{
    $current = arr::get_random($data);
    if ($previous !== $current)
        return true;
    else
        $previous = $current;
}
return false;
