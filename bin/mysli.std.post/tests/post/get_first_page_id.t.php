<?php

#: Define Post
$post = new post('blog/2016/full-post-slug');
$post->__t_set_meta([
    'pages' => [
        'first-page'  => 'First Page',
        'second-page' => 'Second Page'
    ]
]);

#: Test Get First Page IDs
#: Use Post
#: Expect String first-page
return $post->get_first_page_id();
