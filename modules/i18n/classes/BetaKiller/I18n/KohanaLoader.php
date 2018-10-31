<?php
declare(strict_types=1);

namespace BetaKiller\I18n;

class KohanaLoader implements LoaderInterface
{
    /**
     * @var \BetaKiller\I18n\PluralBagFormatterInterface
     */
    private $bagFormatter;

    /**
     * @var \BetaKiller\I18n\PluralBagFactoryInterface
     */
    private $bagFactory;

    /**
     * KohanaLoader constructor.
     *
     * @param \BetaKiller\I18n\PluralBagFormatterInterface $bagFormatter
     * @param \BetaKiller\I18n\PluralBagFactoryInterface   $bagFactory
     */
    public function __construct(
        PluralBagFormatterInterface $bagFormatter,
        PluralBagFactoryInterface $bagFactory
    ) {
        $this->bagFormatter = $bagFormatter;
        $this->bagFactory   = $bagFactory;
    }

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
                    $values = include $file;

                    foreach ($values as $key => $value) {
                        if (\is_array($value)) {
                            // Plural forms are in array
                            $bag = $this->bagFactory->create($value);
                            // Compile with default formatter
                            $values[$key] = $this->bagFormatter->compile($bag);
                        }
                    }

                    // Merge the language strings into the sub table
                    $t[] = $values;
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
