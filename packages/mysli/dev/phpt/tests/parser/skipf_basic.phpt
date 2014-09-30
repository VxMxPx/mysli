--TEST--
Do skipt this test.
--SKIPF--
<?php if (true) { die('Skipped!'); } ?>
--FILE--
<?php
echo "Not skipped!";
?>
--EXPECT--
Should be Skipped...
