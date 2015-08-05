<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;
use mysli\toolkit\fs\fs;

#: Define File
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$file = <<<'FILE'
::use mysli.cm.blog -> blog
::use mysli.cm.page -> page
<div>
    {variable}
</div>
FILE;

#: Define Error
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$file = <<<'FILE'
::use mysli.cms.blog
::use mysli.cms.blog
<div>
    {variable}
</div>
::use mysli.my.blog
<div>
    {variable[2]}
</div>
FILE;

#: Test Use
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#: Use File
$parser = new parser(fs::tmppath('dev.test'));
return assert::equals(
    $parser->template($file),
    <<<'EXPECT'
<?php
use mysli\cm\blog\__tplp as blog;
use mysli\cm\page\__tplp as page;
?><div>
    <?php echo $variable; ?>
</div>
EXPECT
);


#: Test Use Exception
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#: Use Error
#: Expect Exception mysli\tplp\exception\parser 10
$parser = new parser(fs::tmppath('dev.test'));
$parser->template($file);
