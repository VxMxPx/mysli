<?php

#: Define Post
$post = new post('blog/2016/full-post-slug');
$post->__t_set_meta([
    'title' => 'Hello World!'
    ]);

#: Test Get Basic
#: Use Post
#: Expect String Hello World!
return $post->get('title');
