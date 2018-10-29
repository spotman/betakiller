<?php

class HTML
{
    /**
     * @var  array  preferred order of attributes
     */
    private static $attributeOrder = [
        'action',
        'method',
        'type',
        'id',
        'name',
        'value',
        'href',
        'src',
        'width',
        'height',
        'cols',
        'rows',
        'size',
        'maxlength',
        'rel',
        'media',
        'accept-charset',
        'accept',
        'tabindex',
        'accesskey',
        'alt',
        'title',
        'class',
        'style',
        'selected',
        'checked',
        'readonly',
        'disabled',
    ];

    /**
     * @var  boolean  use strict XHTML mode?
     */
    public static $strict = true;

    /**
     * Convert special characters to HTML entities. All untrusted content
     * should be passed through this method to prevent XSS injections.
     *
     *     echo HTML::chars($username);
     *
     * @param   string  $value        string to convert
     * @param   boolean $doubleEncode encode existing entities
     *
     * @return  string
     */
    public static function chars(string $value, bool $doubleEncode = null): string
    {
        return htmlspecialchars($value, ENT_QUOTES, Kohana::$charset, $doubleEncode ?? true);
    }

    /**
     * Convert all applicable characters to HTML entities. All characters
     * that cannot be represented in HTML with the current character set
     * will be converted to entities.
     *
     *     echo HTML::entities($username);
     *
     * @param   string  $value        string to convert
     * @param   boolean $doubleEncode encode existing entities
     *
     * @return  string
     */
    public static function entities(string $value, bool $doubleEncode = null): string
    {
        return htmlentities($value, ENT_QUOTES, Kohana::$charset, $doubleEncode ?? true);
    }

    /**
     * Creates a style sheet link element.
     *
     *     echo HTML::style('media/css/screen.css');
     *
     * @param   string $file       file name
     * @param   array  $attributes default attributes
     *
     * @return  string
     * @uses    HTML::attributes
     */
    public static function style(string $file, array $attributes = null): string
    {
        // Set the stylesheet link
        $attributes['href'] = $file;

        // Set the stylesheet rel
        $attributes['rel'] = empty($attributes['rel']) ? 'stylesheet' : $attributes['rel'];

        // Set the stylesheet type
        $attributes['type'] = 'text/css';

        return '<link'.HTML::attributes($attributes).' />';
    }

    /**
     * Creates a script link.
     *
     *     echo HTML::script('media/js/jquery.min.js');
     *
     * @param   string $file       file name
     * @param   array  $attributes default attributes
     *
     * @return  string
     * @uses    HTML::attributes
     */
    public static function script(string $file, array $attributes = null): string
    {
        // Set the script link
        $attributes['src'] = $file;

        // Set the script type
        $attributes['type'] = 'text/javascript';

        return '<script'.HTML::attributes($attributes).'></script>';
    }

    /**
     * Creates a image link.
     *
     *     echo HTML::image('media/img/logo.png', array('alt' => 'My Company'));
     *
     * @param   string $file       file name
     * @param   array  $attributes default attributes
     *
     * @return  string
     * @uses    HTML::attributes
     */
    public static function image(string $file, array $attributes = null): string
    {
        // Add the image link
        $attributes['src'] = $file;

        return '<img'.HTML::attributes($attributes).' />';
    }

    /**
     * Compiles an array of HTML attributes into an attribute string.
     * Attributes will be sorted using HTML::$attributeOrder for consistency.
     *
     *     echo '<div'.HTML::attributes($attrs).'>'.$content.'</div>';
     *
     * @param   array $attributes attribute list
     *
     * @return  string
     */
    public static function attributes(array $attributes = null): string
    {
        if (empty($attributes)) {
            return '';
        }

        $sorted = [];
        foreach (self::$attributeOrder as $key) {
            if (isset($attributes[$key])) {
                // Add the attribute to the sorted list
                $sorted[$key] = $attributes[$key];
            }
        }

        // Combine the sorted attributes
        $attributes = $sorted + $attributes;

        $compiled = '';
        foreach ($attributes as $key => $value) {
            if ($value === null) {
                // Skip attributes that have NULL values
                continue;
            }

            if (is_int($key)) {
                // Assume non-associative keys are mirrored attributes
                $key = $value;

                if (!HTML::$strict) {
                    // Just use a key
                    $value = false;
                }
            }

            // Add the attribute key
            $compiled .= ' '.$key;

            if ($value || HTML::$strict) {
                // Add the attribute value
                $compiled .= '="'.HTML::chars($value).'"';
            }
        }

        return $compiled;
    }
}
