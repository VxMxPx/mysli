--TEST--
--FILE--
<?php
use mysli\util\i18n\translator;

$data = json_decode(file_get_contents('data.json'), true);
$t = new translator($data, 'en', 'si');
print_r($t->translate('COMMENTS'));
echo "\n";
print_r($t->translate(['COMMENTS', 0]));
echo "\n";
print_r($t->translate(['COMMENTS', 1]));
echo "\n";
print_r($t->translate(['COMMENTS', 2]));
echo "\n";
print_r($t->translate(['COMMENTS', 3]));
echo "\n";
print_r($t->translate(['COMMENTS', 50]));
?>
--EXPECT--
Comments
No comments.
One comment.
2 comments.
3 comments.
50 comments.
