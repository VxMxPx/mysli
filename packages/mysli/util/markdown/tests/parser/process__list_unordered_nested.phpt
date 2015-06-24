--TEST--
--DESCRIPTION--
--FILE--
<?php
use mysli\util\markdown;

echo markdown::process(<<<EOF
- Red
- Green
- Blue
    - Default
        - Saturated
            - Dark
            - Light
        - Desaturated
            - Dark
            - Light
    - Light
        - Saturated
    - Dark
EOF
);

?>
--EXPECT--
<ul>
    <li>Red</li>
    <li>Green</li>
    <li>Blue
        <ul>
            <li>Default
                <ul>
                    <li>Saturated
                        <ul>
                            <li>Dark</li>
                            <li>Light</li>
                        </ul>
                    </li>
                    <li>Desaturated
                        <ul>
                            <li>Dark</li>
                            <li>Light</li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li>Light
                <ul>
                    <li>Saturated</li>
                </ul>
            </li>
            <li>Dark</li>
        </ul>
    </li>
</ul>
