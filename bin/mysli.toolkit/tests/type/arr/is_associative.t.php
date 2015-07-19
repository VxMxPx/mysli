<?php

#: Before
use mysli\toolkit\type\arr;

#: Test Basic, Yes
#: Expect True
return arr::is_associative(['one' => 1, 'two' => 2, 'three' => 3]);

#: Test Basic, No
#: Expect False
return arr::is_associative(['one', 'two', 'three']);

#: Test Empty
#: Expect False
return arr::is_associative([]);

#: Test Empty, Double
#: Expect False
return arr::is_associative([[]]);

#: Test Value is an Empty Array
#: Expect True
return arr::is_associative(['b' => []]);

#: Test Non-zero Start
#: Expect False
return arr::is_associative([2 => 'two']);

#: Test String But Numeric Key
#: Expect False
return arr::is_associative(['2' => 'two']);

#: Test Multi Dimensional
#: Expect False
return arr::is_associative([
    ['name' => 2, 'age' => 3],
    ['name' => 2, 'age' => 3],
]);
