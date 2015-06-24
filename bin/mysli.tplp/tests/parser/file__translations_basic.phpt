--TEST--
--VIRTUAL (test.tplp)--
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
--FILE--
<?php
use mysli\util\tplp\parser;
print_r(parser::file('test.tplp', __DIR__));
?>
--EXPECT--
<?php
namespace tplp\generic\test;
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
