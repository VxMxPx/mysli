<?php

#: Before
use mysli\toolkit\request;
__test_set_server();

#: Test Has Post, No
#: Expect False
return request::has_post();

#: Test Has Post, Yes
#: Expect True
__test_set_post();
return request::has_post();
