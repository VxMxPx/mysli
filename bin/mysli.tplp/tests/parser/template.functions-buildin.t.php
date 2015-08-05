<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;
use mysli\toolkit\fs\fs;

#: Define File
$file = <<<'FILE'
<html>
<body>
    {-12|abs}

    {'hello world!'|ucfirst}
    {'hello world!'|ucwords}
    {'Hello World!'|lower}
    {'Hello World!'|upper}

    {'now'|date:'d.m.y'}
    {user[created_on]|date:'d.m.y'}

    {list|join:','}
    {list|split:','}
    {list|split:',',2}

    {'hello'|length}
    {'hello world'|word_count}
    {animals|count}
    {string|nl2br}

    {12000|number_format}
    {12000|number_format:2}
    {12000|number_format:4, '.', ','}

    {'The %s contains %d monkeys'|replace:'tree',12}

    {3.4|round}
    {3.6|round:0}
    {1.95583|round:2}
    {1241757|round:-3}
    {5.055|round:2}

    {4.3|floor}
    {-3.14|floor}

    {4.3|ceil}
    {-3.14|ceil}

    {'<p>Hello world!</p>'|strip_tags}
    {'<p>Hello world!</p>'|show_tags}
    {'    Hello world!      '|trim}

    {list|slice:0,2}
    {'hello world!'|slice:0,5}

    {'The quick brown fox jumped over the lazy dog.'|word_wrap:20}

    {list|max}
    {10|max:80,30,2}
    {list|min}
    {10|min:80,30,2}

    {records|column:'first_name'}
    {records|column:'first_name','id'}

    {list|reverse}
    {list|contains:'world'}
    {list|key_exists:'id'}
    {list|sum}
    {list|unique}

    {|range:0,6}
    {|random:0,5}
</body>
</html>
FILE;


#: Test Functions Build-in
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#: Use File
$parser = new parser(fs::tmppath('dev.test'));
return assert::equals(
    $parser->template($file),
    <<<'EXPECT'
<html>
<body>
    <?php echo abs(-12); ?>
    <?php echo ucfirst('hello world!'); ?>
    <?php echo ucwords('hello world!'); ?>
    <?php echo strtolower('Hello World!'); ?>
    <?php echo strtoupper('Hello World!'); ?>
    <?php echo date('d.m.y', strtotime('now')); ?>
    <?php echo date('d.m.y', strtotime($user['created_on'])); ?>
    <?php echo implode(',', $list); ?>
    <?php echo explode(',', $list); ?>
    <?php echo explode(',', $list, 2); ?>
    <?php echo strlen('hello'); ?>
    <?php echo str_word_count('hello world'); ?>
    <?php echo count($animals); ?>
    <?php echo nl2br($string); ?>
    <?php echo number_format(12000); ?>
    <?php echo number_format(12000, 2); ?>
    <?php echo number_format(12000, 4, '.', ','); ?>
    <?php echo sprintf('The %s contains %d monkeys', 'tree', 12); ?>
    <?php echo round(3.4); ?>
    <?php echo round(3.6, 0); ?>
    <?php echo round(1.95583, 2); ?>
    <?php echo round(1241757, -3); ?>
    <?php echo round(5.055, 2); ?>
    <?php echo floor(4.3); ?>
    <?php echo floor(-3.14); ?>
    <?php echo ceil(4.3); ?>
    <?php echo ceil(-3.14); ?>
    <?php echo strip_tags('<p>Hello world!</p>'); ?>
    <?php echo htmlspecialchars('<p>Hello world!</p>'); ?>
    <?php echo trim('    Hello world!      '); ?>
    <?php echo ( is_array($list) ? array_slice($list, 0, 2) : substr($list, 0, 2) ); ?>
    <?php echo ( is_array('hello world!') ? array_slice('hello world!', 0, 5) : substr('hello world!', 0, 5) ); ?>
    <?php echo wordwrap('The quick brown fox jumped over the lazy dog.', 20, '<br/>'); ?>
    <?php echo max($list); ?>
    <?php echo max(10, 80, 30, 2); ?>
    <?php echo min($list); ?>
    <?php echo min(10, 80, 30, 2); ?>
    <?php echo array_column($records, 'first_name'); ?>
    <?php echo array_column($records, 'first_name', 'id'); ?>
    <?php echo ( is_array($list) ? array_reverse($list) : strrev($list) ); ?>
    <?php echo ( (is_array($list) ? in_array('world', $list) : strpos($list, 'world')) !== false ); ?>
    <?php echo array_key_exists('id', $list); ?>
    <?php echo array_sum($list); ?>
    <?php echo array_unique($list); ?>
    <?php echo range(0, 6); ?>
    <?php echo rand(0, 5); ?>
</body>
</html>
EXPECT
);
