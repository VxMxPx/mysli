<?php

/**
 * Discover inline tags, like: __bold__, **bold**, _italic_, *italic*
 */
namespace mysli\markdown\module; class inline extends std_module
{
    function process($at)
    {
        $regbag = [
            // Match Escaped
            '/\\\\([^\\\\])/'
            => function ($match)
            {
                return '<<BASE64:'.base64_encode(trim($match[1])).'>>';
            },

            // Match code
            '/(?<!`)(`+)(?!`)(.+?)(?<!`)\1(?!`)/'
            => function ($match)
            {
                return '<code><<BASE64:'.base64_encode(trim($match[2])).'>></code>';
            },

            // Match **bold**
            '/\*\*(?! |\t)(\**.*?\**)(?<! |\t)\*\*/'
            => '<strong>$1</strong>',

            // Match __bold__
            '/(?<![a-zA-Z0-9])__(?! |\\t)(_*.*?_*)(?<! |\\t)__(?![a-zA-Z0-9])/'
            => '<strong>$1</strong>',

            // Match *italic*
            '/\*(?! |\t)(\**.*?\**)(?<! |\t)\*/'
            => '<em>$1</em>',

            // Match _italic_
            '/(?<![a-zA-Z0-9])_(?! |\\t)(_*.*?_*)(?<! |\\t)_(?![a-zA-Z0-9])/'
            => '<em>$1</em>',

            // Match ~~strikethrough~~
            '/(?<!~)~~(?! |\t|~)(.*?)(?<! |\t|~)~~(?!~)/'
            => '<s>$1</s>',

            // Match ~sub~
            '/(?<!~)~(?! |\t|~)(.*?)(?<! |\t|~)~(?!~)/'
            => '<sub>$1</sub>',

            // Match ^sup^
            '/(?<!\^)\^(?! |\t|\^)(.*?)(?<! |\t|\^)\^(?!\^)/'
            => '<sup>$1</sup>',

            // Match ++inserted++
            '/(?<!\+)\+\+(?! |\t|\+)(.*?)(?<! |\t|\+)\+\+(?!\+)/'
            => '<ins>$1</ins>',

            // Match ==marked==
            '/(?<!=)==(?! |\t|=)(.*?)(?<! |\t|=)==(?!=)/'
            => '<mark>$1</mark>',

            // Restore Escaped
            '/<<BASE64:(.*?)>>/'
            => function ($match)
            {
                return base64_decode($match[1]);
            }
        ];

        $this->process_inline($regbag, $at);
    }
}
