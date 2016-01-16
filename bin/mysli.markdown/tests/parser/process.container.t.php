<?php

#: Before
use mysli\markdown;

#: Test Basic
$markdown = <<<MARKDOWN
::: big
Hello world!
:::
MARKDOWN;

return assert::equals(markdown::process($markdown),
'<div class="big">
    <p>Hello world!</p>
</div>');

#: Test Multiple Classes
$markdown = <<<MARKDOWN
::: big.well
Hello world!
:::
MARKDOWN;

return assert::equals(markdown::process($markdown),
'<div class="big well">
    <p>Hello world!</p>
</div>');

#: Test Unclosed
$markdown = <<<MARKDOWN
::: big.well
Hello world!
MARKDOWN;

return assert::equals(markdown::process($markdown),
'<p>::: big.well
    Hello world!</p>');
