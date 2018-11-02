<?php
declare(strict_types=1);

namespace BetaKiller\I18n;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Helper\LoggerHelperTrait;
use Psr\Log\LoggerInterface;

final class I18nFacade
{
    use LoggerHelperTrait;

    public const PLACEHOLDER_PREFIX = ':';

    private const KEY_REGEX = '/^[a-z0-9_]+(?:[\.]{1}[a-z0-9-_]+)+$/m';

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
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \BetaKiller\I18n\TranslatorInterface
     */
    private $translator;

    public function __construct(
        AppConfigInterface $appConfig,
        LoggerInterface $logger,
        TranslatorInterface $translator
    ) {
        $this->appConfig  = $appConfig;
        $this->logger     = $logger;
        $this->translator = $translator;

        $this->init();
    }

    private function init(): void
    {
        $this->languagesConfig  = $this->appConfig->getAllowedLanguages();
        $this->allowedLanguages = \array_keys($this->languagesConfig);

        if (!$this->allowedLanguages) {
            throw new \RuntimeException('Define app languages in config/app.php');
        }

        // Set default language locale as a fallback
        $defaultLang   = $this->getDefaultLanguage();
        $defaultLocale = $this->getLanguageLocale($defaultLang);
        $this->translator->setFallbackLocale($defaultLocale);
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
        if (!self::isI18nKey($key)) {
            throw new I18nException('String ":value" is not an i18 key', [':value' => $key]);
        }

        $locale = $this->getLanguageLocale($lang);

        $string = $this->getValue($key, $locale);

//        if ($values) {
//            // Add prefix if does not exists
//            $values = self::addPlaceholderPrefixToKeys($values);
//        }

        return $this->replacePlaceholders($string, $values);
    }

    public function pluralize(string $lang, string $key, $form, array $values = null): string
    {
        $locale = $this->getLanguageLocale($lang);

        // Detect form name if a $form is an integer-like
        if ((string)(int)$form === (string)$form) {
            // Detect form based on a count
            $form = \Punic\Plural::getRule($form, $locale);
        }

        $string = $this->translator->pluralize($key, $form, $locale);

        return $this->replacePlaceholders($string, $values);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public static function isI18nKey(string $key): bool
    {
        return (bool)preg_match(self::KEY_REGEX, $key);
    }

    public static function addPlaceholderPrefixToKeys(array $data): array
    {
        $output = [];

        foreach ($data as $key => $value) {
            // Add prefix if it does not exist
            if (strpos($key, self::PLACEHOLDER_PREFIX) !== 0) {
                $key = self::PLACEHOLDER_PREFIX.$key;
            }

            $output[$key] = $value;
        }

        return $output;
    }

    private function replacePlaceholders(string $string, ?array $values): string
    {
        if (empty($values)) {
            return $string;
        }

        return strtr($string, array_filter($values, 'is_scalar'));
    }

    /**
     * Returns translation of a string. If no translation exists, the original
     * string will be returned. No parameters are replaced.
     *
     * @param   string $key    text to translate
     * @param   string $locale target locale
     *
     * @return  string
     */
    private function getValue(string $key, string $locale): string
    {
        if (!$key) {
            return '';
        }

        try {
            return $this->translator->translate($key, $locale);
        } catch (I18nException $e) {
            // Translated string does not exist
            // Store exception with the original key as missing
            $this->logException($this->logger, $e);

            // Empty string instead of a key
            return '';
        }
    }
}
