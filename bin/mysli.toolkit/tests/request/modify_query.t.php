<?php

#: Before
use mysli\toolkit\request;

#: Test Modify Query
#: Expect String ?get1=val1&get2=new-val-2
__test_set_server();
return request::modify_query([ 'get2' => 'new-val-2' ]);

#: Test Modify Query, Add New Element
#: Expect String ?get1=val1&get2=val2&get3=val3
__test_set_server();
return request::modify_query([ 'get3' => 'val3' ]);
