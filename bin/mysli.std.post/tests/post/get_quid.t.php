<?php

#: Define Post
$post = new post('blog/2016/full-post-slug');

#: Test Get QUID
#: Use Post
#: Expect String blog/2016/full-post-slug
return $post->get_quid();
