<?php

#: Before
use mysli\toolkit\request;
__test_set_server();

#: Test Server Value
#: Expect String HTTP/1.1
return request::server('server_protocol');

#: Test Server Value, Default
#: Expect String default
return request::server('non_existant', 'default');
