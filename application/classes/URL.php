<?php

class URL
{
    /**
     * @param string      $string
     * @param string|null $delimiter
     *
     * @return null|string
     * @see https://github.com/ausi/slug-generator
     */
    public static function transliterate(string $string, string $delimiter = null): ?string
    {
        $delimiter = $delimiter ?? '-';

        $tr = [
            'А' => 'a',
            'Б' => 'b',
            'В' => 'v',
            'Г' => 'g',
            'Д' => 'd',
            'Е' => 'e',
            'Ж' => 'j',
            'З' => 'z',
            'И' => 'i',
            'Й' => 'y',
            'К' => 'k',
            'Л' => 'l',
            'М' => 'm',
            'Н' => 'n',
            'О' => 'o',
            'П' => 'p',
            'Р' => 'r',
            'С' => 's',
            'Т' => 't',
            'У' => 'u',
            'Ф' => 'f',
            'Х' => 'h',
            'Ц' => 'ts',
            'Ч' => 'ch',
            'Ш' => 'sh',
            'Щ' => 'sch',
            'Ъ' => '',
            'Ы' => 'yi',
            'Ь' => '',
            'Э' => 'e',
            'Ю' => 'yu',
            'Я' => 'ya',
            'а' => 'a',
            'б' => 'b',
            'в' => 'v',
            'г' => 'g',
            'д' => 'd',
            'е' => 'e',
            'ж' => 'j',
            'з' => 'z',
            'и' => 'i',
            'й' => 'y',
            'к' => 'k',
            'л' => 'l',
            'м' => 'm',
            'н' => 'n',
            'о' => 'o',
            'п' => 'p',
            'р' => 'r',
            'с' => 's',
            'т' => 't',
            'у' => 'u',
            'ф' => 'f',
            'х' => 'h',
            'ц' => 'ts',
            'ч' => 'ch',
            'ш' => 'sh',
            'щ' => 'sch',
            'ъ' => 'y',
            'ы' => 'yi',
            'ь' => '',
            'э' => 'e',
            'ю' => 'yu',
            'я' => 'ya',
            ' ' => '-',
            '.' => '-',
            '/' => '-',
        ];

        // Replace by table
        $clean = strtr($string, $tr);

        // Replace all incorrect symbols
        $clean = preg_replace('/[^0-9A-Za-z-]+/', $delimiter, $clean);

        // Final cleanup
        $clean = strtolower(trim($clean, $delimiter));

        return $clean;
    }
}
