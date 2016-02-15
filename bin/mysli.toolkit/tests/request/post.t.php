<?php

#: Before
use mysli\toolkit\request;
__test_set_server();
__test_set_post();

#: Test Post One
#: Expect String val1
return request::post('post1');

#: Test Post Default
#: Expect String Default Value
return request::post('non_existent', 'Default Value');

#: Test Post All
return assert::equals(
    request::post(),
    [ 'post1' => 'val1', 'post2' => 'val2' ]);

#: Test Post Multiple
return assert::equals(
    request::post(['post1', 'post2']),
    [ 'val1', 'val2' ]);

#: Test Post Multiple, Non Existent
return assert::equals(
    request::post(['post1', 'post2', 'non_existent']),
    [ 'val1', 'val2', null ]);
