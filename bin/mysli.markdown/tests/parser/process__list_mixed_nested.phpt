--TEST--
--DESCRIPTION--
--FILE--
<?php
use mysli\util\markdown;

echo markdown::process(<<<EOF
- One
    1. A
    2. B
        - Sub-One
        - Sub-Two
            1. Sub A
            2. Sub B
    3. C
- Two
- Three
EOF
);
?>
--EXPECT--
<ul>
    <li>One
        <ol>
            <li>A</li>
            <li>B
                <ul>
                    <li>Sub-One</li>
                    <li>Sub-Two
                        <ol>
                            <li>Sub A</li>
                            <li>Sub B</li>
                        </ol>
                    </li>
                </ul>
            </li>
            <li>C</li>
        </ol>
    </li>
    <li>Two</li>
    <li>Three</li>
</ul>
