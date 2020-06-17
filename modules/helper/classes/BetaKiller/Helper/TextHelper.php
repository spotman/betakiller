<?php
declare(strict_types=1);

namespace BetaKiller\Helper;

use Transliterator;

class TextHelper
{
    /**
     * @param string $utf8
     *
     * @return string
     * @see http://php.net/manual/ru/function.iconv.php#105507
     * @see http://php.net/manual/ru/function.mb-convert-encoding.php#80620
     * @see https://stackoverflow.com/a/35178045
     */
    public static function utf8ToAscii(string $utf8): string
    {
        $trans = Transliterator::createFromRules(
            ':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: Lower(); :: NFC;',
            Transliterator::FORWARD
        );

        return $trans->transliterate($utf8);
    }

    public static function prepareCodename(string $value, int $limit): string
    {
        $value = \mb_strtolower(self::utf8ToAscii($value));

        $replace = [
            '!' => '_bang_',
            '@' => '_dog_',
            '#' => '_hash_',
            '$' => '_dollar_',
            '%' => '_percent_',
            '^' => '_up_',
            '&' => '_amp_',
            '*' => '_snow_',
            '+' => '_plus_',
            '-' => '_',
            '=' => '_eq_',
            '/' => '_slash_',
            ':' => '_colon_',
            ';' => '_semicolon_',
        ];

        $value = \strtr($value, $replace);

        $value = \preg_replace(
            [
                // Replace whitespaces with underscore
                '/[\s]+/',
                // Keep only alpha and underscore symbols
                '/[^a-z0-9_]+/',
            ],
            [
                '_',
                '',
            ],
            $value
        );

        // Remove excessive underscores
        $value = trim($value, '_');

        if ($limit) {
            $value = \Text::limit_chars($value, $limit, '', false);
        }

        return $value;
    }

    public static function startsWith(string $haystack, string $needle): bool
    {
        $length = strlen($needle);

        return $length && strpos($haystack, $needle) === 0;
    }

    public static function endsWith(string $haystack, string $needle): bool
    {
        return substr($haystack, -strlen($needle)) === $needle;
    }

    public static function contains(string $haystack, string $needle): bool
    {
        return \strpos($haystack, $needle) !== false;
    }
}
