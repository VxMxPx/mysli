<?php

#: Before
use mysli\toolkit\request;
__test_set_server();

#: Test Uri
#: Expect String /index.php/seg1/seg2?get1=val1&get2=val2
return request::uri();
