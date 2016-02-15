<?php

#: Before
use mysli\toolkit\request;
__test_set_server();

#: Test Is SSL, No
#: Expect False
return request::is_ssl();

#: Test Is SSL, Yes Post
#: Expect True
$_SERVER['SERVER_PORT'] = '443';
return request::is_ssl();

#: Test Is SSL, Yes HTTPS => on
#: Expect True
$_SERVER['HTTPS'] = 'on';
return request::is_ssl();
