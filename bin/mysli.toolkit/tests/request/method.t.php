<?php

#: Before
use mysli\toolkit\request;
__test_set_server();

#: Test Get
#: Expect String GET
return request::method();

#: Test Post
#: Expect String POST
__test_set_post();
return request::method();

#: Test Put
#: Expect String PUT
__test_set_server();
$_SERVER['REQUEST_METHOD'] = 'PUT';
return request::method();

#: Test Put, Fake
#: Expect String PUT
__test_set_server();
$_POST['REQUEST_METHOD'] = 'PUT';
return request::method( true );

#: Test Delete
#: Expect String DELETE
__test_set_server();
$_SERVER['REQUEST_METHOD'] = 'DELETE';
return request::method();

#: Test Delete, Fake
#: Expect String DELETE
__test_set_server();
$_POST['REQUEST_METHOD'] = 'DELETE';
return request::method( true );
