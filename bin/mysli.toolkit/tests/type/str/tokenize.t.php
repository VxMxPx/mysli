<?php

#: Before
use mysli\toolkit\type\str;

#: Test Basic
return assert::equals(
    str::tokenize('class="primary button big" id="register" value="Register"', ' ', '"'),
    [ 'class="primary button big"', 'id="register"', 'value="Register"' ]
);

#: Test Open-Close
return assert::equals(
    str::tokenize('(group 1) (group 2) (group 3)', ' ', ['(', ')']),
    [ '(group 1)', '(group 2)', '(group 3)' ]
);
