--TEST--
Do NOT skipt this test.
--SKIPF--
<?php if (false) { die('Skipped!'); } ?>
--FILE--
<?php
echo "Not skipped!";
?>
--EXPECT--
Not skipped!
