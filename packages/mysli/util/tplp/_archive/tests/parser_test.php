<?php

namespace Mysli\Tplp;

include(__DIR__.'/../../core/core.php');
new \Mysli\Core\Core(
    realpath(__DIR__.'/dummy'),
    realpath(__DIR__.'/dummy')
);
include(__DIR__.'/../exceptions/parser_exception.php');
include(__DIR__.'/../parser.php');

class ParserTest extends \PHPUnit_Framework_TestCase
{
    private function instance_parse($template)
    {
        date_default_timezone_set('UTC');
        $instance = new \Mysli\Tplp\Parser($template);
        return $instance->parse();
    }

    private function eval_me($exp, $variables = null)
    {
        if ($variables) {
            foreach ($variables as $evvar => $evval) {
                $$evvar = $evval;
            }
        }
        return eval('return ' . substr($exp, 10, -3));
    }

    // Various -----------------------------------------------------------------

    public function test_parse_comments()
    {
        $this->assertEquals(
            'No comment!',
            $this->instance_parse('{* Comment one! *}No comment!{* Comment two! *}')
        );
    }

    public function test_parse_comments_multiline()
    {
        $this->assertEquals(
            'No comment!',
            $this->instance_parse('{* This is
            a multiline comment! *}No comment!{*And Comment
             two!*}')
        );
    }

    public function test_parse_escape()
    {
        $this->assertEquals(
            "{, }, ', 'I\\'m in string...'",
            $this->instance_parse("\\{, \\}, \\', 'I\\'m in string...'")
        );
    }

    public function test_parse_raw_regions()
    {
        $this->assertEquals(
            '{hello_world}',
            $this->instance_parse('{{{ {hello_world} }}}')
        );
    }

    // Variables ---------------------------------------------------------------

    public function test_parse_variable()
    {
        $this->assertEquals(
            '<?php echo $hello_world; ?>',
            $this->instance_parse('{hello_world}')
        );
    }

    public function test_parse_variable_array()
    {
        $this->assertEquals(
            '<?php echo $users[\'id\']; ?>',
            $this->instance_parse('{users[id]}')
        );
    }

    public function test_parse_variable_array_escaped()
    {
        $this->assertEquals(
            '<?php echo $users[$id]; ?>',
            $this->instance_parse('{users[\\$id]}')
        );
    }

    public function test_parse_variable_property()
    {
        $this->assertEquals(
            '<?php echo $user->id; ?>',
            $this->instance_parse('{user->id}')
        );
    }

    public function test_parse_variable_function()
    {
        $this->assertEquals(
            '<?php echo strtolower($username); ?>',
            $this->instance_parse('{username|lower}')
        );
    }

    public function test_parse_variable_function_chain()
    {
        $this->assertEquals(
            '<?php echo ucfirst(strtolower($username)); ?>',
            $this->instance_parse('{username|lower|ucfirst}')
        );
    }

    public function test_parse_variable_function_with_variables()
    {
        $this->assertEquals(
            '<?php echo ( is_array($username) ? array_slice($username, $start, $end) : substr($username, $start, $end) ); ?>',
            $this->instance_parse('{username|slice:start,end}')
        );
    }

    public function test_parse_variable_function_with_params()
    {
        $this->assertEquals(
            '<?php echo $tplp_func_my_func($username, \'string\', 25, true); ?>',
            $this->instance_parse('{username|my_func:\'string\', 25, true}')
        );
    }

    public function test_parse_variable_function_chain_with_params()
    {
        $this->assertEquals(
            '<?php echo $tplp_func_x($tplp_func_x($tplp_func_x($tplp_func_x($tplp_func_x($username, 22.2), \'string\'), $variable), false), null); ?>',
            $this->instance_parse('{username|x:22.2|x:\'string\'|x:variable|x:false|x:null}')
        );
    }

    // Control -----------------------------------------------------------------

    public function test_parse_control_if()
    {
        $this->assertEquals(
            '<?php if (true): ?>
                <?php echo $hello; ?>!
            <?php endif; ?>',
            $this->instance_parse('::if true
                {hello}!
            ::/if')
        );
    }

    public function test_parse_control_if_variable_to_variable()
    {
        $this->assertEquals(
            '<?php if ($var1 === $var2): ?>
                <?php echo $hello; ?>!
            <?php endif; ?>',
            $this->instance_parse('::if var1 == var2
                {hello}!
            ::/if')
        );
    }

    public function test_parse_control_if_array_to_boolean()
    {
        $this->assertEquals(
            '<?php if ($user[\'uname\'] !== false): ?>
                <?php echo $hello; ?>!
            <?php endif; ?>',
            $this->instance_parse('::if user[uname] != false
                {hello}!
            ::/if')
        );
    }

    public function test_parse_control_if_not_or_and()
    {
        $this->assertEquals(
            '<?php if ((!$me AND !$you) OR !false): ?>
                ...
            <?php endif; ?>',
            $this->instance_parse('::if (!me AND !you) OR !false
                ...
            ::/if')
        );
    }

    public function test_parse_control_if_else()
    {
        $this->assertEquals(
            '<?php if ($me === $you): ?>
                <?php echo $me; ?> equals <?php echo $you; ?>!
            <?php else: ?>
                <?php echo $me; ?> not equals <?php echo $you; ?>!
            <?php endif; ?>',
            $this->instance_parse('::if me == you
                {me} equals {you}!
            ::else
                {me} not equals {you}!
            ::/if')
        );
    }

    public function test_parse_control_if_elif_else()
    {
        $this->assertEquals(
            '<?php if ($number === 12): ?>
                12!
            <?php elseif ($number === 14): ?>
                14!
            <?php else: ?>
                Something else!
            <?php endif; ?>',
            $this->instance_parse('::if number == 12
                12!
            ::elif number == 14
                14!
            ::else
                Something else!
            ::/if')
        );
    }

    public function test_parse_control_if_functions()
    {
        $this->assertEquals(
            '<?php if (strlen($uname) > 10): ?>
                Wow, more than 10!
            <?php endif; ?>',
            $this->instance_parse('::if uname|length > 10
                Wow, more than 10!
            ::/if')
        );
    }

    public function test_parse_control_if_functions_advanced()
    {
        $this->assertEquals(
            '<?php if (( (is_array($uname) ? in_array($code, $uname) : strpos($uname, $code)) !== false )): ?>
                ...
            <?php endif; ?>',
            $this->instance_parse('::if uname|contains:code
                ...
            ::/if')
        );
    }

    public function test_parse_control_if_functions_chain()
    {
        $this->assertEquals(
            '<?php if (ucfirst(strtolower(( is_array($uname) ? array_slice($uname, 0, 10) : substr($uname, 0, 10) ))) !== \'Lazy Piano\'): ?>
                ...
            <?php endif; ?>',
            $this->instance_parse('::if uname|slice:0,10|lower|ucfirst != \'Lazy Piano\'
                ...
            ::/if')
        );
    }

    public function test_parse_control_for()
    {
        $this->assertEquals(
            '<?php foreach ($users as $user): ?>
                ...
            <?php endforeach; ?>',
            $this->instance_parse('::for user in users
                ...
            ::/for')
        );
    }

    public function test_parse_control_for_key()
    {
        $this->assertEquals(
            '<?php foreach ($users as $uid => $user): ?>
                ...
            <?php endforeach; ?>',
            $this->instance_parse('::for uid, user in users
                ...
            ::/for')
        );
        // No space between key and value!
        $this->assertEquals(
            '<?php foreach ($users as $uid => $user): ?>
                ...
            <?php endforeach; ?>',
            $this->instance_parse('::for uid,user in users
                ...
            ::/for')
        );
    }

    public function test_parse_control_for_adcanced()
    {
        $this->assertEquals(
            '<?php foreach ($collection[\'main\']->users as $uid => $user): ?>
                <?php if ($user->uname): ?>
                    <?php echo $user->uname; ?>
                    <?php continue; ?>
                <?php elseif ($user->fullname): ?>
                    <?php echo $user->fullname; ?>
                    <?php break; ?>
                <?php else: ?>
                    Anonymous!
                <?php endif; ?>
            <?php endforeach; ?>',
            $this->instance_parse('::for uid, user in collection[main]->users
                ::if user->uname
                    {user->uname}
                    ::continue
                ::elif user->fullname
                    {user->fullname}
                    ::break
                ::else
                    Anonymous!
                ::/if
            ::/for')
        );
    }

    public function test_parse_control_for_with_function()
    {
        $this->assertEquals(
            '<?php foreach (explode(\'||\', $sources) as $source): ?>
                <?php echo trim($source); ?>
            <?php endforeach; ?>',
            $this->instance_parse('::for source in sources|split:\'||\'
                {source|trim}
            ::/for')
        );
    }

    public function test_parse_control_for_with_multiple_function()
    {
        $this->assertEquals(
            '<?php foreach (( is_array(explode(\'||\', $sources)) ? array_slice(explode(\'||\', $sources), 0, 5) : substr(explode(\'||\', $sources), 0, 5) ) as $source): ?>
                <?php echo trim($source); ?>
            <?php endforeach; ?>',
            $this->instance_parse('::for source in sources|split:\'||\'|slice:0,5
                {source|trim}
            ::/for')
        );
    }

    // Translations ------------------------------------------------------------

    public function test_parse_translations()
    {
        $this->assertEquals(
            '<?php echo $tplp_translator_service(\'HELLO_WORLD\'); ?>',
            $this->instance_parse('{@HELLO_WORLD}')
        );
    }

    public function test_parse_translations_pluralization()
    {
        $this->assertEquals(
            '<?php echo $tplp_translator_service([\'HELLO_WORLD\', 12]); ?>',
            $this->instance_parse('{@HELLO_WORLD(12)}')
        );
    }

    public function test_parse_translations_variable_function()
    {
        $this->assertEquals(
            '<?php echo $tplp_translator_service([\'HELLO_WORLD\', count($comments)]); ?>',
            $this->instance_parse('{@HELLO_WORLD(comments|count)}')
        );
    }

    public function test_parse_translations_parameter()
    {
        $this->assertEquals(
            '<?php echo $tplp_translator_service(\'HELLO_WORLD\', [\'Hello!\']); ?>',
            $this->instance_parse('{@HELLO_WORLD \'Hello!\'}')
        );
    }

    public function test_parse_translations_parameter_variable()
    {
        $this->assertEquals(
            '<?php echo $tplp_translator_service(\'HELLO_WORLD\', [$hello]); ?>',
            $this->instance_parse('{@HELLO_WORLD hello}')
        );
    }

    public function test_parse_translations_parameters_mixed()
    {
        $this->assertEquals(
            '<?php echo $tplp_translator_service(\'HELLO_WORLD\', [$user[\'uname\'], 12]); ?>',
            $this->instance_parse('{@HELLO_WORLD user[uname], 12}')
        );
    }

    public function test_parse_translations_parameters_mixed_multiline()
    {
        $this->assertEquals(
            '<?php echo $tplp_translator_service([\'HELLO_WORLD\', 34], [$user->uname, \'string\', 12]); ?>',
            $this->instance_parse('{@HELLO_WORLD(34)
                user->uname,
                \'string\',
                12}')
        );
    }

    // Test build in functions -------------------------------------------------

    public function test_parse_buildin_abs()
    {
        $result = $this->instance_parse('{-12|abs}');

        $this->assertEquals(
            '<?php echo abs(-12); ?>',
            $result
        );

        $this->assertEquals(
            12,
            $this->eval_me($result)
        );
    }

    public function test_parse_buildin_ucfirst()
    {
        $result = $this->instance_parse('{hello|ucfirst}');

        $this->assertEquals(
            '<?php echo ucfirst($hello); ?>',
            $result
        );

        $this->assertEquals(
            'Hello',
            $this->eval_me($result, [
                'hello' => 'hello'
            ])
        );
    }

    public function test_parse_buildin_ucwords()
    {
        $result = $this->instance_parse('{hello|ucwords}');

        $this->assertEquals(
            '<?php echo ucwords($hello); ?>',
            $result
        );

        $this->assertEquals(
            'Hello World!',
            $this->eval_me($result, [
                'hello' => 'hello world!'
            ])
        );
    }

    public function test_parse_buildin_lower()
    {
        $result = $this->instance_parse('{hello|lower}');

        $this->assertEquals(
            '<?php echo strtolower($hello); ?>',
            $result
        );

        $this->assertEquals(
            'hello world',
            $this->eval_me($result, [
                'hello' => 'HELLO World'
            ])
        );
    }

    public function test_parse_buildin_upper()
    {
        $result = $this->instance_parse('{hello|upper}');

        $this->assertEquals(
            '<?php echo strtoupper($hello); ?>',
            $result
        );

        $this->assertEquals(
            'HELLO WORLD',
            $this->eval_me($result, [
                'hello' => 'hello world'
            ])
        );
    }

    public function test_parse_buildin_date()
    {
        $result = $this->instance_parse('{user[created_on]|date:\'Y-m-d\'}');

        $this->assertEquals(
            '<?php echo date(\'Y-m-d\', strtotime($user[\'created_on\'])); ?>',
            $result
        );

        $this->assertEquals(
            '2014-04-02',
            $this->eval_me($result, [
                'user' => [
                    'created_on' => '20140402123345'
                ]
            ])
        );
    }

    public function test_parse_buildin_join()
    {
        $result = $this->instance_parse('{list|join:\',\'}');

        $this->assertEquals(
            '<?php echo implode(\',\', $list); ?>',
            $result
        );

        $this->assertEquals(
            'one,two,three',
            $this->eval_me($result, [
                'list' => ['one', 'two', 'three']
            ])
        );
    }

    /**
     * @expectedException Mysli\Tplp\ParserException
     */
    public function test_parse_buildin_join_missing_parameter()
    {
        $this->assertEquals(
            '<?php echo implode(%1, $list); ?>',
            $this->instance_parse('{list|join}')
        );
    }

    public function test_parse_buildin_split()
    {
        $result = $this->instance_parse('{string|split:\',\'}');

        $this->assertEquals(
            '<?php echo explode(\',\', $string); ?>',
            $result
        );

        $this->assertEquals(
            ['one', 'two', 'three'],
            $this->eval_me($result, [
                'string' => 'one,two,three'
            ])
        );
    }

    public function test_parse_buildin_split_limit()
    {
        $result = $this->instance_parse('{string|split:\',\',2}');

        $this->assertEquals(
            '<?php echo explode(\',\', $string, 2); ?>',
            $result
        );

        $this->assertEquals(
            ['one', 'two,three'],
            $this->eval_me($result, [
                'string' => 'one,two,three'
            ])
        );
    }

    public function test_parse_buildin_length()
    {
        $result = $this->instance_parse('{string|length}');

        $this->assertEquals(
            '<?php echo strlen($string); ?>',
            $result
        );

        $this->assertEquals(
            11,
            $this->eval_me($result, [
                'string' => 'hello world'
            ])
        );
    }

    public function test_parse_buildin_word_count()
    {
        $result = $this->instance_parse('{string|word_count}');

        $this->assertEquals(
            '<?php echo str_word_count($string); ?>',
            $result
        );

        $this->assertEquals(
            12,
            $this->eval_me($result, [
                'string' => 'What saves a man, is to take a step. Then another step.'
            ])
        );
    }

    public function test_parse_buildin_count()
    {
        $result = $this->instance_parse('{list|count}');

        $this->assertEquals(
            '<?php echo count($list); ?>',
            $result
        );

        $this->assertEquals(
            6,
            $this->eval_me($result, [
                'list' => [1, 2, 3, 4, 5, 6]
            ])
        );
    }

    public function test_parse_buildin_nl2br()
    {
        $result = $this->instance_parse('{text|nl2br}');

        $this->assertEquals(
            '<?php echo nl2br($text); ?>',
            $result
        );

        $this->assertEquals(
            "hello<br />\nworld",
            $this->eval_me($result, [
                'text' => "hello\nworld"
            ])
        );
    }

    public function test_parse_buildin_number_format()
    {
        $result = $this->instance_parse('{12000|number_format:4,\',\',\'.\'}');

        $this->assertEquals(
            '<?php echo number_format(12000, 4, \',\', \'.\'); ?>',
            $result
        );

        $this->assertEquals(
            '12.000,0000',
            $this->eval_me($result)
        );
    }

    public function test_parse_buildin_number_format_one_param()
    {
        $result = $this->instance_parse('{12000|number_format:2}');

        $this->assertEquals(
            '<?php echo number_format(12000, 2); ?>',
            $result
        );

        $this->assertEquals(
            '12,000.00',
            $this->eval_me($result)
        );
    }

    public function test_parse_buildin_number_format_no_params()
    {
        $result = $this->instance_parse('{12000|number_format}');

        $this->assertEquals(
            '<?php echo number_format(12000); ?>',
            $result
        );

        $this->assertEquals(
            '12,000',
            $this->eval_me($result)
        );
    }

    public function test_parse_buildin_number_format_too_many_params()
    {
        $result = $this->instance_parse('{12000|number_format:2,null,null,null,null}');

        $this->assertEquals(
            '<?php echo number_format(12000, 2, null, null, null, null); ?>',
            $result
        );

        // Error in function...
    }

    public function test_parse_buildin_replace()
    {
        $result = $this->instance_parse('{\'The %2$s contains %1$d monkeys\'|replace:num,location}');

        $this->assertEquals(
            '<?php echo sprintf(\'The %2$s contains %1$d monkeys\', $num, $location); ?>',
            $result
        );

        $this->assertEquals(
            'The tree contains 12 monkeys',
            $this->eval_me($result, [
                'num'      => 12,
                'location' => 'tree'
            ])
        );
    }

    public function test_parse_buildin_round()
    {
        $result = $this->instance_parse('{3.14159265359|round:4}');

        $this->assertEquals(
            '<?php echo round(3.14159265359, 4); ?>',
            $result
        );

        $this->assertEquals(
            3.1416,
            $this->eval_me($result)
        );
    }

    public function test_parse_buildin_floor()
    {
        $result = $this->instance_parse('{4.32|floor}');

        $this->assertEquals(
            '<?php echo floor(4.32); ?>',
            $result
        );

        $this->assertEquals(
            4,
            $this->eval_me($result)
        );
    }

    public function test_parse_buildin_ceil()
    {
        $result = $this->instance_parse('{var|ceil}');

        $this->assertEquals(
            '<?php echo ceil($var); ?>',
            $result
        );

        $this->assertEquals(
            5,
            $this->eval_me($result, [
                'var' => 4.556
            ])
        );
    }

    public function test_parse_buildin_strip_tags()
    {
        $result = $this->instance_parse('{var|strip_tags}');

        $this->assertEquals(
            '<?php echo strip_tags($var); ?>',
            $result
        );

        $this->assertEquals(
            'Hello World!',
            $this->eval_me($result, [
                'var' => '<strong>Hello</strong> <a href="#">World</a>!'
            ])
        );
    }

    public function test_parse_buildin_show_tags()
    {
        $result = $this->instance_parse('{var|show_tags}');

        $this->assertEquals(
            '<?php echo htmlspecialchars($var); ?>',
            $result
        );

        $this->assertEquals(
            '&lt;strong&gt;Hello&lt;/strong&gt; &lt;a href=&quot;#&quot;&gt;World&lt;/a&gt;!',
            $this->eval_me($result, [
                'var' => '<strong>Hello</strong> <a href="#">World</a>!'
            ])
        );
    }

    public function test_parse_buildin_trim()
    {
        $result = $this->instance_parse('{var|trim}');
        $this->assertEquals(
            '<?php echo trim($var); ?>',
            $result
        );

        $this->assertEquals(
            'Hello World!',
            $this->eval_me($result, [
                'var' => ' Hello World!      '
            ])
        );
    }

    public function test_parse_buildin_slice()
    {
        $result = $this->instance_parse('{variable|slice:0,-7}');

        $this->assertEquals(
            '<?php echo ( is_array($variable) ? array_slice($variable, 0, -7) : substr($variable, 0, -7) ); ?>',
            $result
        );

        $this->assertEquals(
            'Hello',
            $this->eval_me($result, [
                'variable' => 'Hello World!'
            ])
        );
    }

    public function test_parse_buildin_word_wrap()
    {
        $result = $this->instance_parse('{variable|word_wrap:14}');

        $this->assertEquals(
            '<?php echo wordwrap($variable, 14, \'<br/>\'); ?>',
            $result
        );

        $this->assertEquals(
            'Lorem ipsum<br/>dolor sit<br/>amet,<br/>consectetur<br/>adipisicing<br/>elit.',
            $this->eval_me($result, [
                'variable' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.'
            ])
        );
    }

    public function test_parse_buildin_max()
    {
        $result = $this->instance_parse('{list|max}');

        $this->assertEquals(
            '<?php echo max($list); ?>',
            $result
        );

        $this->assertEquals(
            120,
            $this->eval_me($result, [
                'list' => [12, 45, 84, 2, 120]
            ])
        );
    }

    public function test_parse_buildin_max_variables()
    {
        $result = $this->instance_parse('{length|max:12,var[length]}');

        $this->assertEquals(
            '<?php echo max($length, 12, $var[\'length\']); ?>',
            $result
        );

        $this->assertEquals(
            123,
            $this->eval_me($result, [
                'length' => 122.4,
                'var'    => ['length' => 123]
            ])
        );
    }

    public function test_parse_buildin_min()
    {
        $result = $this->instance_parse('{list|min}');

        $this->assertEquals(
            '<?php echo min($list); ?>',
            $result
        );

        $this->assertEquals(
            -123.4,
            $this->eval_me($result, [
                'list' => [0, -12, 345, 0.4, -123.4]
            ])
        );
    }

    public function test_parse_buildin_column()
    {
        $result = $this->instance_parse('{list|column:key}');

        $this->assertEquals(
            '<?php echo array_column($list, $key); ?>',
            $result
        );

        $this->assertEquals(
            ['John', 'Sally', 'Jane', 'Peter'],
            $this->eval_me($result, [
                'list' => [
                    [ 'id' => 2135, 'first_name' => 'John',  'last_name' => 'Doe'   ],
                    [ 'id' => 3245, 'first_name' => 'Sally', 'last_name' => 'Smith' ],
                    [ 'id' => 5342, 'first_name' => 'Jane',  'last_name' => 'Jones' ],
                    [ 'id' => 5623, 'first_name' => 'Peter', 'last_name' => 'Doe'   ]
                ],
                'key'  => 'first_name'
            ])
        );
    }

    public function test_parse_buildin_column_second_argument()
    {
        $result = $this->instance_parse('{list|column:val,key}');

        $this->assertEquals(
            '<?php echo array_column($list, $val, $key); ?>',
            $result
        );

        $this->assertEquals(
            [2135 => 'John', 3245 => 'Sally', 5342 => 'Jane', 5623 => 'Peter'],
            $this->eval_me($result, [
                'list' => [
                    [ 'id' => 2135, 'first_name' => 'John',  'last_name' => 'Doe'   ],
                    [ 'id' => 3245, 'first_name' => 'Sally', 'last_name' => 'Smith' ],
                    [ 'id' => 5342, 'first_name' => 'Jane',  'last_name' => 'Jones' ],
                    [ 'id' => 5623, 'first_name' => 'Peter', 'last_name' => 'Doe'   ]
                ],
                'val' => 'first_name',
                'key' => 'id'
            ])
        );
    }

    public function test_parse_buildin_reverse()
    {
        $result = $this->instance_parse('{list|reverse}');

        $this->assertEquals(
            '<?php echo ( is_array($list) ? array_reverse($list) : strrev($list) ); ?>',
            $result
        );

        $this->assertEquals(
            [4, 3, 2, 1],
            $this->eval_me($result, [
                'list' => [1, 2, 3, 4]
            ])
        );
    }

    public function test_parse_buildin_contains()
    {
        $result = $this->instance_parse('{list|contains:\'world\'}');

        $this->assertEquals(
            '<?php echo ( (is_array($list) ? in_array(\'world\', $list) : strpos($list, \'world\')) !== false ); ?>',
            $result
        );

        $this->assertTrue(
            $this->eval_me($result, [
                'list' => ['hello', 'world']
            ])
        );

        $this->assertFalse(
            $this->eval_me($result, [
                'list' => ['hello', 'mooon']
            ])
        );

        $this->assertTrue(
            $this->eval_me($result, [
                'list' => 'world, hello!'
            ])
        );

        $this->assertFalse(
            $this->eval_me($result, [
                'list' => 'moon, hello!'
            ])
        );
    }

    public function test_parse_buildin_key_exists()
    {
        $result = $this->instance_parse('{list|key_exists:\'id\'}');

        $this->assertEquals(
            '<?php echo array_key_exists(\'id\', $list); ?>',
            $result
        );

        $this->assertTrue(
            $this->eval_me($result, [
                'list' => ['id' => 12, 'name' => 'Marko']
            ])
        );

        $this->assertFalse(
            $this->eval_me($result, [
                'list' => ['name' => 'Marko']
            ])
        );
    }

    public function test_parse_buildin_sum()
    {
        $result = $this->instance_parse('{list|sum}');
        $this->assertEquals(
            '<?php echo array_sum($list); ?>',
            $result
        );

        $this->assertEquals(
            10,
            $this->eval_me($result, [
                'list' => [1, 2, 3, 4]
            ])
        );
    }

    public function test_parse_buildin_unique()
    {
        $result = $this->instance_parse('{list|unique}');

        $this->assertEquals(
            '<?php echo array_unique($list); ?>',
            $result
        );

        $this->assertEquals(
            [1, 2, 3 => 3, 5 => 4],
            $this->eval_me($result, [
                'list' => [1, 2, 1, 3, 1, 4, 3, 4]
            ])
        );
    }

}
