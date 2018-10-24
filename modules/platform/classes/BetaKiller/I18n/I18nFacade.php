<?php
declare(strict_types=1);

namespace BetaKiller\I18n;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Exception;
use BetaKiller\Helper\LoggerHelperTrait;
use Psr\Log\LoggerInterface;

final class I18nFacade
{
    use LoggerHelperTrait;

    public const PLACEHOLDER_PREFIX = ':';

    private const KEY_REGEX = '/^[a-z0-9_]+(?:[\.]{1}[a-z0-9_]+)+$/m';

    /**
     * @var \BetaKiller\Config\AppConfigInterface
     */
    private $appConfig;

    /**
     * "lang codename" => "default locale"
     *
     * @var array
     */
    private $languagesConfig;

    /**
     * @var string[]
     */
    private $allowedLanguages;

    /**
     * @var  array  cache of loaded languages
     */
    private static $cache = [];

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(AppConfigInterface $appConfig, LoggerInterface $logger)
    {
        $this->appConfig = $appConfig;
        $this->logger    = $logger;

        $this->init();
    }

    private function init(): void
    {
        $this->languagesConfig  = $this->appConfig->getAllowedLanguages();
        $this->allowedLanguages = \array_keys($this->languagesConfig);

        if (!$this->allowedLanguages) {
            throw new \RuntimeException('Define app languages in config/app.php');
        }
    }

    public function hasLanguage(string $lang): bool
    {
        return isset($this->languagesConfig[$lang]);
    }

    public function getDefaultLanguage(): string
    {
        // First language is primary
        return $this->allowedLanguages[0];
    }

    public function getAllowedLanguages(): array
    {
        return $this->allowedLanguages;
    }

    public function getLanguageLocale(string $lang): string
    {
        return $this->languagesConfig[$lang];
    }

    public function translate(string $lang, string $key, array $values = null): string
    {
        if (!$this->isI18nKey($key)) {
            throw new Exception('String ":value" is not an i18 key', [':value' => $key]);
        }

        $string = $this->getValue($key, $lang);

//        if ($values) {
//            // Add prefix if does not exists
//            $values = self::addPlaceholderPrefixToKeys($values);
//        }

        return empty($values) ? $string : strtr($string, $values);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function isI18nKey(string $key): bool
    {
        return (bool)preg_match(self::KEY_REGEX, $key);
    }

    /**
     * @param string      $key
     * @param string|null $lang
     *
     * @return bool
     */
    public function has(string $key, string $lang): bool
    {
        // Load the translation table for this language
        $table = $this->getTranslations($lang);

        return isset($table[$key]);
    }

//    private static function addPlaceholderPrefixToKeys(array $data): array
//    {
//        $output = [];
//
//        foreach ($data as $key => $value) {
//            // Add prefix if it does not exist
//            if (strpos($key, self::PLACEHOLDER_PREFIX) !== 0) {
//                $key = self::PLACEHOLDER_PREFIX.$key;
//            }
//
//            $output[$key] = $value;
//        }
//
//        return $output;
//    }

    /**
     * Returns translation of a string. If no translation exists, the original
     * string will be returned. No parameters are replaced.
     *
     * @param   string $key  text to translate
     * @param   string $lang target language
     *
     * @return  string
     */
    private function getValue(string $key, string $lang): string
    {
        if (!$key) {
            return '';
        }

        // Load the translation table for this language
        $table = $this->getTranslations($lang);

        // Return the translated string if it exists
        if (isset($table[$key])) {
            return $table[$key];
        }

        // Translated string does not exist
        // Store exception with the original key as missing
        $this->logException(
            $this->logger,
            new \RuntimeException(sprintf('Missing i18n key "%s" for lang "%s"', $key, $lang))
        );

        // Empty string instead of key
        return '';
    }

    /**
     * Returns the translation table for a given language.
     *
     * @param   string $lang language to load
     *
     * @return  array
     */
    private function getTranslations(string $lang): array
    {
        if (isset(self::$cache[$lang])) {
            return self::$cache[$lang];
        }

        // New translation table
        $table = [];

        // Split the language: language, region, locale, etc
        $parts = explode('-', $lang);

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

        // Cache the translation table locally
        return self::$cache[$lang] = $table;
    }
}
