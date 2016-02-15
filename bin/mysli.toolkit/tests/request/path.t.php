<?php

#: Before
use mysli\toolkit\request;
__test_set_server();

#: Test Path
#: Expect String /seg1/seg2
return request::path();

#: Test Path, No PATH_INFO
#: Expect String /seg1/seg2
unset($_SERVER['PATH_INFO']);
return request::path();
