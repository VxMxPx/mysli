--TEST--
--VIRTUAL (test.tplp)--
::use mysli.cms.blog
::use mysli.cms.blog
<div>
    {variable}
</div>
::use mysli.my.blog
<div>
    {variable[2]}
</div>
--FILE--
<?php
use mysli\util\tplp\parser;
print_r(parser::file('test.tplp', __DIR__));
?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\util\tplp\exception\parser' with message 'Cannot use `mysli.my.blog` as `blog` because the name is already previously declared in following location(s):
>>1. ::use mysli.cms.blog
  2. ::use mysli.cms.blog
  3. <div>
  4.     {variable}
File `emp/phpt/test.tplp`

  1. ::use mysli.cms.blog
>>2. ::use mysli.cms.blog
  3. <div>
  4.     {variable}
  5. </div>
File `emp/phpt/test.tplp`

ERROR:
  3. <div>
  4.     {variable}
  5. </div>
>>6. ::use mysli.my.blog
  7. <div>
  8.     {variable[2]}
  9. </div>
File: `emp/phpt/test.tplp`
' in %sparser.php:%d
Stack trace:
%a
