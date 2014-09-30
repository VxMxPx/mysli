--TEST--
Do NOT skipt this test.
--SKIPIF--
<?php if (false) { die('Skipped!'); } ?>
--FILE--
<?php
echo "Not skipped!";
?>
--EXPECT--
Not skipped!
