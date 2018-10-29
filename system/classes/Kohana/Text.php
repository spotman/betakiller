<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Text helper class. Provides simple methods for working with text.
 *
 * @package    Kohana
 * @category   Helpers
 * @author     Kohana Team
 * @copyright  (c) 2007-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Text {

	/**
	 * Limits a phrase to a given number of characters.
	 *
	 *     $text = Text::limit_chars($text);
	 *
	 * @param   string  $str            phrase to limit characters of
	 * @param   integer $limit          number of characters to limit to
	 * @param   string  $end_char       end character or entity
	 * @param   boolean $preserve_words enable or disable the preservation of words while limiting
	 * @return  string
	 * @uses    UTF8::strlen
	 */
	public static function limit_chars($str, $limit = 100, $end_char = NULL, $preserve_words = FALSE)
	{
		$end_char = ($end_char === NULL) ? '…' : $end_char;

		$limit = (int) $limit;

		if (trim($str) === '' OR UTF8::strlen($str) <= $limit)
			return $str;

		if ($limit <= 0)
			return $end_char;

		if ($preserve_words === FALSE)
			return rtrim(UTF8::substr($str, 0, $limit)).$end_char;

		// Don't preserve words. The limit is considered the top limit.
		// No strings with a length longer than $limit should be returned.
		if ( ! preg_match('/^.{0,'.$limit.'}\s/us', $str, $matches))
			return $end_char;

		return rtrim($matches[0]).((strlen($matches[0]) === strlen($str)) ? '' : $end_char);
	}

	/**
	 * Generates a random string of a given type and length.
	 *
	 *
	 *     $str = Text::random(); // 8 character random string
	 *
	 * The following types are supported:
	 *
	 * alnum
	 * :  Upper and lower case a-z, 0-9 (default)
	 *
	 * alpha
	 * :  Upper and lower case a-z
	 *
	 * hexdec
	 * :  Hexadecimal characters a-f, 0-9
	 *
	 * distinct
	 * :  Uppercase characters and numbers that cannot be confused
	 *
	 * You can also create a custom type by providing the "pool" of characters
	 * as the type.
	 *
	 * @param   string  $type   a type of pool, or a string of characters to use as the pool
	 * @param   integer $length length of string to return
	 * @return  string
	 * @uses    UTF8::split
	 */
	public static function random($type = NULL, $length = 8)
	{
		if ($type === NULL)
		{
			// Default is to generate an alphanumeric string
			$type = 'alnum';
		}

		$utf8 = FALSE;

		switch ($type)
		{
			case 'alnum':
				$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			break;
			case 'alpha':
				$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			break;
			case 'hexdec':
				$pool = '0123456789abcdef';
			break;
			case 'numeric':
				$pool = '0123456789';
			break;
			case 'nozero':
				$pool = '123456789';
			break;
			case 'distinct':
				$pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
			break;
			default:
				$pool = (string) $type;
				$utf8 = ! UTF8::is_ascii($pool);
			break;
		}

		// Split the pool into an array of characters
		$pool = ($utf8 === TRUE) ? UTF8::str_split($pool, 1) : str_split($pool, 1);

		// Largest pool key
		$max = count($pool) - 1;

		$str = '';
		for ($i = 0; $i < $length; $i++)
		{
			// Select a random character from the pool and add it to the string
			$str .= $pool[mt_rand(0, $max)];
		}

		// Make sure alnum strings contain at least one letter and one digit
		if ($type === 'alnum' AND $length > 1)
		{
			if (ctype_alpha($str))
			{
				// Add a random digit
				$str[mt_rand(0, $length - 1)] = chr(mt_rand(48, 57));
			}
			elseif (ctype_digit($str))
			{
				// Add a random letter
				$str[mt_rand(0, $length - 1)] = chr(mt_rand(65, 90));
			}
		}

		return $str;
	}
} // End text
