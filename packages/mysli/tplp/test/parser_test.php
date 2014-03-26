<?php

namespace Mysli\Tplp;

include(__DIR__.'/../parser.php');
include(__DIR__.'/../../core/core.php'); // CORE needed for utility!
new \Mysli\Core(
    realpath(__DIR__.'/dummy'),
    realpath(__DIR__.'/dummy')
);

class ParserTest extends \PHPUnit_Framework_TestCase
{
    private function instance_parse($template)
    {
        $instance = new \Mysli\Tplp\Parser($template);
        return $instance->parse();
    }

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
            $this->instance_parse('{username|strtolower}')
        );
    }

    public function test_parse_variable_function_chain()
    {
        $this->assertEquals(
            '<?php echo ucfirst(strtolower($username)); ?>',
            $this->instance_parse('{username|strtolower|ucfirst}')
        );
    }

    public function test_parse_variable_function_with_variables()
    {
        $this->assertEquals(
            '<?php echo substr($username, $start, $end); ?>',
            $this->instance_parse('{username|substr:start,end}')
        );
    }

    public function test_parse_variable_function_with_params()
    {
        $this->assertEquals(
            '<?php echo my_func($username, \'string\', 25, true); ?>',
            $this->instance_parse('{username|my_func:\'string\', 25, true}')
        );
    }

    public function test_parse_variable_function_chain_with_params()
    {
        $this->assertEquals(
            '<?php echo x(x(x(x(x($username, 22.2), \'string\'), $variable), false), null); ?>',
            $this->instance_parse('{username|x:22.2|x:\'string\'|x:variable|x:false|x:null}')
        );
    }

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
            $this->instance_parse('::if uname|strlen > 10
                Wow, more than 10!
            ::/if')
        );
    }

    public function test_parse_control_if_functions_chain()
    {
        $this->assertEquals(
            '<?php if (ucfirst(strtolower(substr($uname, 0, 10))) !== \'Lazy Piano\'): ?>
                ...
            <?php endif; ?>',
            $this->instance_parse('::if uname|substr:0,10|strtolower|ucfirst != \'Lazy Piano\'
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

    public function test_parse_raw_regions()
    {
        $this->assertEquals(
            '{hello_world}',
            $this->instance_parse('{{{ {hello_world} }}}')
        );
    }

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

}
