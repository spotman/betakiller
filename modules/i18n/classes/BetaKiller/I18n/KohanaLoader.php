<?php
declare(strict_types=1);

namespace BetaKiller\I18n;

class KohanaLoader implements LoaderInterface
{
    /**
     * Returns "key" => "translated string" pairs for provided locale
     *
     * @param string $locale
     *
     * @return string[]
     */
    public function load(string $locale): array
    {
        // New translation table
        $table = [];

        // Split the language: language, region, locale, etc
        $parts = explode('-', $locale);

        do {
            // Create a path for this set of parts
            $path = implode(DIRECTORY_SEPARATOR, $parts);

            $files = \Kohana::find_file('i18n', $path, null, true);

            if ($files) {
                $t = [];
                foreach ($files as $file) {
                    /** @noinspection PhpIncludeInspection */
                    // Merge the language strings into the sub table
                    $t[] = include $file;
                }

                // Append the sub table, preventing less specific language
                // files from overloading more specific files
                $table += array_merge(...$t);
            }

            // Remove the last part
            array_pop($parts);
        } while ($parts);

        return $table;
    }
}
