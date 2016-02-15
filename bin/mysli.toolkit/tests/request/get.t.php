<?php

#: Before
use mysli\toolkit\request;
__test_set_server();

#: Test Get One
#: Expect String val1
return request::get('get1');

#: Test Get Default
#: Expect String Default Value
return request::get('non_existent', 'Default Value');

#: Test Get All
return assert::equals(
    request::get(),
    [ 'get1' => 'val1', 'get2' => 'val2' ]);

#: Test Get Multiple
return assert::equals(
    request::get(['get1', 'get2']),
    [ 'val1', 'val2' ]);

#: Test Get Multiple, Non Existent
return assert::equals(
    request::get(['get1', 'get2', 'non_existent']),
    [ 'val1', 'val2', null ]);
