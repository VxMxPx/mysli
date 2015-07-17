<?php

namespace mysli\toolkit; class html
{
    /**
     * Convert special characters to HTML entities.
     * This method is using: `htmlspecialchars`, to convert following entities:
     * '&' -> '&amp;',
     * '"' -> '&quot;' (when $quotes not 0)
     * "'" -> '&#039;' (or &apos;) (when $quotes is 2)
     * '<' -> '&lt;'
     * '>' -> '&gt;'
     * You can use PHP flags.
     * --
     * @param string  $string
     * @param integer $flags
     * --
     * @return string
     */
    static function entities_encode($string, $flags=ENT_QUOTES|ENT_SUBSTITUTE|ENT_HTML401)
    {
        return htmlspecialchars($string, $flags);
    }

    /**
     * Convert all special characters to HTML entities.
     * This method is using: `htmlentities`, to convert all special characters.
     * --
     * @param string  $string
     * @param integer $flags
     * --
     * @return string
     */
    static function entities_encode_all($string, $flags=ENT_QUOTES|ENT_SUBSTITUTE|ENT_HTML401)
    {
        return htmlentities($string, $flags);
    }

    /**
     * Decode HTML entities back to special characters.
     * --
     * @param string  $string
     * @param integer $flags
     * --
     * @return string
     */
    static function entities_decode($string, $flags=ENT_QUOTES|ENT_SUBSTITUTE|ENT_HTML401)
    {
        return htmlspecialchars_decode($string, $flags);
    }

    /**
     * Decode all HTML entities back to special characters.
     * --
     * @param string $string
     * @param integer $flags
     * --
     * @return string
     */
    static function entities_decode_all($string, $flags=ENT_QUOTES|ENT_SUBSTITUTE|ENT_HTML401)
    {
        return html_entity_decode($string, $flags);
    }

    /**
     * Output an HTML tag.
     * --
     * @example
     * When:
     *     // Anchor tag:
     *     html::tag('a', ['href' => 'http://domain.tld'], 'Link!');
     *     // No content means tag will self close.
     *     html::tag('hr');
     *     // Empty content, will produce seperate closing tag.
     *     html::tag('div', [], '');
     * Will return:
     *     <a href="http://domain.tld">Link!</a>
     *     <hr />
     *     <div></div>
     * --
     * @param string $tag
     * @param array  $attributes
     * @param string $content
     * --
     * @return string
     */
    static function tag($tag, $attributes=[], $content=null)
    {
        $tagc = "<{$tag}";

        foreach ($attributes as $attr => $value)
        {
            $tagc .= " {$attr}=\"{$value}\"";
        }

        if ($content !== null)
        {
            $tagc .= ">\n{$content}</{$tag}>";
        }
        else
        {
            $tagc .= "/>";
        }

        return $tagc;
    }

    /**
     * Inserts HTML line breaks before all newlines in a string.
     * --
     * @example
     *     Hello\nWorld => Hello<br/>\nWorld
     * --
     * @param string $string
     * --
     * @return string
     */
    static function nl_to_br($string)
    {
        return nl2br($string, true);
    }

    /**
     * Strip HTML and PHP tags from a string.
     * --
     * @param string $string
     * @param string $allowed e.g. <p><a><div>
     * --
     * @return string
     */
    static function strip_tags($string, $allowed=null)
    {
        return $allowed
            ? strip_tags($string, $allowed)
            : strip_tags($string);
    }
}
