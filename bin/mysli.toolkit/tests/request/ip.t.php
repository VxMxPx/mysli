<?php

#: Before
use mysli\toolkit\request;
__test_set_server();

#: Test IP
#: Expect String 192.168.1.12
return request::ip();
