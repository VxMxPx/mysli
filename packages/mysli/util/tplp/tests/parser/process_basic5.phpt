--TEST--
Translations
--FILE--
<?php
use mysli\util\tplp\parser;

$input = <<<INPUT
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
INPUT;

print_r(parser::process($input));
?>
--EXPECT--
<html>
<body>
    <?php echo $tplp_translator_service('HELLO'); ?>
    <?php echo $tplp_translator_service(['COMMENTS', $comments_count]); ?>
    <?php echo $tplp_translator_service(['COMMENTS', $comments->count]); ?>
    <?php echo $tplp_translator_service(['COMMENTS', $comments['count']]); ?>
    <?php echo $tplp_translator_service(['COMMENTS', count($comments)]); ?>
    <?php echo $tplp_translator_service('HELLO_USER', [$user1, $user2, false, 'string!']); ?>
</body>
</html>
