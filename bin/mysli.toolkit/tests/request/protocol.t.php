<?php

#: Before
use mysli\toolkit\request;
__test_set_server();

#: Test Protocol
#: Expect String HTTP/1.1
return request::protocol();

#: Test Protocol, Different
#: Expect String HTTP/1.0
$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
return request::protocol();
