<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;

#: Test Translation
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$template = <<<'TEMPLATE'
<html>
<body>
    {@HELLO}
    {@COMMENTS(comments_count)}
    {@COMMENTS(comments->count)}
    {@COMMENTS(comments['count'])}
    {@COMMENTS(comments|count)}
    {@HELLO_USER user1, user2, false, 'string!'}
</body>
</html>
TEMPLATE;
$parser = new parser();
return assert::equals(
    $parser->process($template),
    <<<'EXPECT'
<html>
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
