<?php

#: Before
use mysli\toolkit\request;
__test_set_server();

#: Test Time
#: Expect Integer 1455530846
return request::time();
