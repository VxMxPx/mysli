<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;
use mysli\toolkit\fs\fs;

#: Define File
$file = <<<'FILE'
<html>
<body>
    {@HELLO}
    {@COMMENTS(comments_count)}
    {@COMMENTS(comments->count)}
    {@COMMENTS(comments[count])}
    {@COMMENTS(comments|count)}
    {@HELLO_USER user1, user2, false, 'string!'}
</body>
</html>
FILE;

#: Test Translation
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#: Use File
$processed = parser::file(
    '~test.tpl.html',
    fs::tmppath('dev.test'),
    [ '~test' => $file ]
);
return assert::equals(
    $processed,
    <<<'EXPECT'
<?php
namespace tplp\template\test;
?><html>
<body>
    <?php echo $tplp_func_translator_service('HELLO'); ?>
    <?php echo $tplp_func_translator_service(['COMMENTS', $comments_count]); ?>
    <?php echo $tplp_func_translator_service(['COMMENTS', $comments->count]); ?>
    <?php echo $tplp_func_translator_service(['COMMENTS', $comments['count']]); ?>
    <?php echo $tplp_func_translator_service(['COMMENTS', count($comments)]); ?>
    <?php echo $tplp_func_translator_service('HELLO_USER', [$user1, $user2, false, 'string!']); ?>
</body>
</html>
EXPECT
);
