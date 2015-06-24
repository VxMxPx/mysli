--TEST--
--DESCRIPTION--
--FILE--
<?php
use mysli\util\markdown;

echo markdown::process(<<<EOF
- One
    - Two
        - Two B
    - Three
- Four
EOF
);
?>
--EXPECT--
<ul>
    <li>One
        <ul>
            <li>Two
                <ul>
                    <li>Two B</li>
                </ul>
            </li>
            <li>Three</li>
        </ul>
    </li>
    <li>Four</li>
</ul>
