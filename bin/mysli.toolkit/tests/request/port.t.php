<?php

#: Before
use mysli\toolkit\request;
__test_set_server();

#: Test Port
#: Expect Integer 80
return request::port();
