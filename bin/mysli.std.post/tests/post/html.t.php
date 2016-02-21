<?php

#: Define Post
$post = new post('blog/2016/full-post-slug');
$post->__t_set_meta([ 'pages' => [ '_def' => [], 'page' => [] ] ]);
$post->__t_set_html([ '_def' => 'Testing HTML', 'page' => 'Page HTML!' ]);

#: Test Get HTML
#: Use Post
#: Expect String Testing HTML
return $post->html();

#: Test Get Specific HTML
#: Use Post
#: Expect String Page HTML!
return $post->html('page');
