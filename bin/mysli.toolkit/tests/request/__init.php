<?php

function __test_set_server()
{
    $_SERVER = [
        'DOCUMENT_ROOT'   => '/home/domain/public_html',
        'REMOTE_ADDR'     => '192.168.1.12', // ::1
        'REMOTE_PORT'     => '37156',
        'SERVER_SOFTWARE' => 'PHP 7.0.3 Development Server', // Apache
        'SERVER_PROTOCOL' => 'HTTP/1.1',
        'SERVER_NAME'     => 'domain.tld', // localhost, IP
        'SERVER_PORT'     => '80', // 443, 8000, ...
        'REQUEST_URI'     => '/index.php/seg1/seg2?get1=val1&get2=val2', // /, script.php, ...
        'REQUEST_METHOD'  => 'GET', // POST, DELETE, PUT
        'SCRIPT_NAME'     => '/index.php', // script.php, ...
        'SCRIPT_FILENAME' => '/home/domain/public_html/index.php', // ...
        'PATH_INFO'       => '/seg1/seg2', // Might NOT exists
        'PHP_SELF'        => '/index.php/seg1/seg1', // /index.php, /script.php
        'QUERY_STRING'    => 'get1=val1&get2=val2', // Might NOT exists, or be empty
        'HTTP_HOST'       => 'domain.tld', // localhost:8000, IP, ...
        'HTTP_CONNECTION' => 'keep-alive',
        'HTTP_ACCEPT'     => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Linux x86_64)',
        'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, sdch',
        'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.8,sl;q=0.6',
        'REQUEST_TIME_FLOAT'   => 1455530846.553,
        'REQUEST_TIME'         => 1455530846
    ];
    $_GET = [
        'get1' => 'val1',
        'get2' => 'val2',
    ];
    $_POST = [];
}
function __test_set_post()
{
    $_SERVER['REQUEST_METHOD']  = 'POST';
    $_POST = [
        'post1' => 'val1',
        'post2' => 'val2',
    ];
}
