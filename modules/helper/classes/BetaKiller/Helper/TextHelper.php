<?php
declare(strict_types=1);

namespace BetaKiller\Helper;

class TextHelper
{
    /**
     * @param string $utf8
     *
     * @return string
     * @see http://php.net/manual/ru/function.iconv.php#105507
     * @see http://php.net/manual/ru/function.mb-convert-encoding.php#80620
     */
    public static function utf8ToAscii(string $utf8): string
    {
        return \mb_convert_encoding($utf8, 'ASCII');
    }
}
