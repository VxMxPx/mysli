<?php

#: Define Post
$post = new post('blog/2016/full-post-slug');
$post->__t_set_source(
// META
[
    'title' => 'Sample Blog Post',
    'date'  => '2015-03-30',
    'tags'  => [ 'traveling', 'test' ]
],
// SOURCE
'Introduction
------------

Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
proident, sunt in culpa qui officia deserunt mollit anim id est laborum.');

#: Test Get All Meta
#: Use Post
return assert::equals(
    $post->meta(null),
    [
        'title' => 'Sample Blog Post',
        'date' => '2015-03-30',
        'tags' => [ 'traveling', 'test' ],
        'references' => [ '_default' => [] ],
        'table_of_contents' => [
            '_default' => [
                'introduction' => [
                    'id'    => 'introduction',
                    'fid'   => 'introduction',
                    'title' => 'Introduction',
                    'level' => 2,
                    'items' => []
                ]
            ]
        ],
        'pages' => [
            '_default' => [
                'title'     => 'Introduction',
                'quid'      => '_default',
                'fquid'     => '2016/full-post-slug/_default',
                'index'     => 1,
                'next'      => null,
                'previous'  => null,
                'is_first'  => true,
                'is_last'   => true,
                'is_single' => true,
            ]
        ],
        '__hash_new' => '4b5cc523a5bd61848fa2f193b574b889',
        'quid' => '2016/full-post-slug'
    ]
);

#: Test Get Page Specific Meta
#: Use Post
return assert::equals(
    $post->meta(),
    [
        'title' => 'Sample Blog Post',
        'date' => '2015-03-30',
        'tags' => [ 'traveling', 'test' ],
        'references' => [ '_default' => [] ],
        'table_of_contents' => [
            '_default' => [
                'introduction' => [
                    'id'    => 'introduction',
                    'fid'   => 'introduction',
                    'title' => 'Introduction',
                    'level' => 2,
                    'items' => []
                ]
            ]
        ],
        'pages' => [
            '_default' => [
                'title'     => 'Introduction',
                'quid'      => '_default',
                'fquid'     => '2016/full-post-slug/_default',
                'index'     => 1,
                'next'      => null,
                'previous'  => null,
                'is_first'  => true,
                'is_last'   => true,
                'is_single' => true,
            ]
        ],
        '__hash_new' => '4b5cc523a5bd61848fa2f193b574b889',
        'quid' => '2016/full-post-slug',
        'page' => [
            'title'     => 'Introduction',
            'quid'      => '_default',
            'fquid'     => '2016/full-post-slug/_default',
            'index'     => 1,
            'next'      => null,
            'previous'  => null,
            'is_first'  => true,
            'is_last'   => true,
            'is_single' => true,
            'table_of_contents' => [
                'introduction' => [
                    'id'    => 'introduction',
                    'fid'   => 'introduction',
                    'title' => 'Introduction',
                    'level' => 2,
                    'items' => []
                ]
            ],
            'references' => []
        ]
    ]
);
