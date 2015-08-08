--TEST--
Numbers + variables
--FILE--
<?php
use mysli\util\i18n\parser;

print_r(parser::parse(<<<FILE
# Numbers + variables
@COMMENTS     Comments
@COMMENTS[0]  No comments.
@COMMENTS[1]  One comment.
@COMMENTS[2+] {n} comments.

@NUMBERS[*7]  I'm ending with 7!
@NUMBERS[4*]  I'm starting with 4!
@NUMBERS[1*2] I'm starting with 1 and ending with 2!

@ODD[*1,*3,*5,*7,*9] I'm odd! :S
@TWO_AND_NINE[2,9]   Two or nine!
FILE
));

?>
--EXPECTF--
Array
(
    [.meta] => Array
        (
            [created_on] => %d
            [modified] =>%s
        )

    [COMMENTS] => Array
        (
            [value] => Comments
            [0] => Array
                (
                    [value] => No comments.
                )

            [1] => Array
                (
                    [value] => One comment.
                )

            [2+] => Array
                (
                    [value] => {n} comments.
                )

        )

    [NUMBERS] => Array
        (
            [*7] => Array
                (
                    [value] => I'm ending with 7!
                )

            [4*] => Array
                (
                    [value] => I'm starting with 4!
                )

            [1*2] => Array
                (
                    [value] => I'm starting with 1 and ending with 2!
                )

        )

    [ODD] => Array
        (
            [*1] => Array
                (
                    [value] => I'm odd! :S
                )

            [*3] => Array
                (
                    [value] => I'm odd! :S
                )

            [*5] => Array
                (
                    [value] => I'm odd! :S
                )

            [*7] => Array
                (
                    [value] => I'm odd! :S
                )

            [*9] => Array
                (
                    [value] => I'm odd! :S
                )

        )

    [TWO_AND_NINE] => Array
        (
            [2] => Array
                (
                    [value] => Two or nine!
                )

            [9] => Array
                (
                    [value] => Two or nine!
                )

        )

)
