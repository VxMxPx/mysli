<?php

#: Define Post
$post = new post('blog/2016/full-post-slug');

#: Test Get Media URI
#: Use Post
#: Expect String blog/2016/full-post-slug/media/picture.jpg
return $post->media_uri('picture.jpg');
