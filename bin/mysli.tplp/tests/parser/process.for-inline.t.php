<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;

#: Test For Inline
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$template = <<<'TEMPLATE'
{post in posts}
{header in page['headers']}
{post|upper in posts}
TEMPLATE;
$parser = new parser();
return assert::equals(
    $parser->process($template),
    <<<'EXPECT'
<?php foreach ($posts as $post): echo $post; endforeach; ?>
<?php foreach ($page['headers'] as $header): echo $header; endforeach; ?>
<?php foreach ($posts as $post): echo strtoupper($post); endforeach; ?>
EXPECT
);
