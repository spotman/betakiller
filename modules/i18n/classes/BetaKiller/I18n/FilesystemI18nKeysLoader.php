<?php
declare(strict_types=1);

namespace BetaKiller\I18n;

use BetaKiller\Model\LanguageInterface;
use BetaKiller\Repository\LanguageRepository;

class FilesystemI18nKeysLoader implements I18nKeysLoaderInterface
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
     * @var \BetaKiller\Repository\LanguageRepository
     */
    private $langRepo;

    /**
     * FilesystemI18nKeysLoader constructor.
     *
     * @param \BetaKiller\Repository\LanguageRepository    $langRepo
     * @param \BetaKiller\I18n\PluralBagFormatterInterface $bagFormatter
     * @param \BetaKiller\I18n\PluralBagFactoryInterface   $bagFactory
     */
    public function __construct(
        LanguageRepository $langRepo,
        PluralBagFormatterInterface $bagFormatter,
        PluralBagFactoryInterface $bagFactory
    ) {
        $this->langRepo     = $langRepo;
        $this->bagFormatter = $bagFormatter;
        $this->bagFactory   = $bagFactory;
    }

    /**
     * Returns keys models with all translations
     *
     * @return \BetaKiller\Model\I18nKeyInterface[]
     */
    public function loadI18nKeys(): array
    {
        $keys = [];

        foreach ($this->langRepo->getAll() as $lang) {
            foreach ($this->getLangData($lang) as $keyName => $i18nValue) {
                // Create key if not exists
                $key = $keys[$keyName] ?? $keys[$keyName] = new I18nKey($keyName);

                $key->setI18nValue($lang, $i18nValue);
            }
        }

        return $keys;
    }

    /**
     * Returns "key" => "translated string" pairs for provided locale
     *
     * @param \BetaKiller\Model\LanguageInterface $lang
     *
     * @return string[]
     */
    private function getLangData(LanguageInterface $lang): array
    {
        // New translation table
        $table = [];

        // Split the language: language, region, locale, etc
        $parts = explode('-', $lang->getLocale());

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
