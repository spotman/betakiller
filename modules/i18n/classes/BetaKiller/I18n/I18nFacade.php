<?php

declare(strict_types=1);

namespace BetaKiller\I18n;

use BetaKiller\Helper\LoggerHelper;
use BetaKiller\Model\HasI18nKeyNameInterface;
use BetaKiller\Model\I18nKeyInterface;
use BetaKiller\Model\LanguageInterface;
use BetaKiller\Repository\LanguageRepositoryInterface;
use LogicException;
use Psr\Log\LoggerInterface;
use Punic\Plural;
use RuntimeException;

final class I18nFacade
{
    public const ROLE_TRANSLATOR = 'translator';

    public const PLACEHOLDER_PREFIX = ':';

    // Placeholder for primary language ISO code
    public const PRIMARY_LANG_ISO = 'primary';

    public const KEY_REGEX = '/^[a-z0-9-]+(?:[\.]{1}[a-z0-9-+]+)+$/m';

    /**
     * @var \BetaKiller\Model\LanguageInterface[]
     */
    private array $languages;

    /**
     * @var string[]
     */
    private array $languagesIsoCodes;

    /**
     * @var LanguageInterface
     */
    private LanguageInterface $primaryLang;

    /**
     * @var LanguageInterface|null
     */
    private ?LanguageInterface $fallbackLang;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var \BetaKiller\Repository\LanguageRepositoryInterface
     */
    private LanguageRepositoryInterface $langRepo;

    /**
     * @var \BetaKiller\I18n\PluralBagFormatterInterface
     */
    private PluralBagFormatterInterface $formatter;

    /**
     * @var \BetaKiller\I18n\I18nKeysLoaderInterface
     */
    private I18nKeysLoaderInterface $loader;

    /**
     * @var I18nKeyInterface[]
     */
    private array $keysCache = [];

    /**
     * @var \BetaKiller\I18n\I18nConfigInterface
     */
    private I18nConfigInterface $config;

    /**
     * I18nFacade constructor.
     *
     * @param \BetaKiller\Repository\LanguageRepositoryInterface $langRepo
     * @param \BetaKiller\I18n\PluralBagFormatterInterface       $formatter
     * @param \BetaKiller\I18n\I18nKeysLoaderInterface           $loader
     * @param \BetaKiller\I18n\I18nConfigInterface               $config
     * @param \Psr\Log\LoggerInterface                           $logger
     */
    public function __construct(
        LanguageRepositoryInterface $langRepo,
        PluralBagFormatterInterface $formatter,
        I18nKeysLoaderInterface $loader,
        I18nConfigInterface $config,
        LoggerInterface $logger
    ) {
        $this->langRepo  = $langRepo;
        $this->loader    = $loader;
        $this->formatter = $formatter;
        $this->config    = $config;
        $this->logger    = $logger;

        $this->init();
    }

    private function init(): void
    {
        foreach ($this->langRepo->getAppLanguages() as $lang) {
            $this->languages[$lang->getIsoCode()] = $lang;
        }

        if (!$this->languages) {
            throw new RuntimeException('Define languages first and import them via import:languages task');
        }

        $this->languagesIsoCodes = array_keys($this->languages);

        // First language is primary (default language is a fallback)
        $this->primaryLang = reset($this->languages);

        // Define fallback language for translating missing keys
        $fallbackIsoCode = $this->config->getFallbackLanguage();

        if ($fallbackIsoCode) {
            if (!isset($this->languages[$fallbackIsoCode])) {
                throw new RuntimeException(
                    sprintf('Can not use "%s" as a fallback language; add it to allowed languages', $fallbackIsoCode)
                );
            }

            $this->fallbackLang = $this->languages[$fallbackIsoCode];
        }
    }

    public function hasLanguage(string $lang): bool
    {
        return in_array($lang, $this->languagesIsoCodes, true);
    }

    public function getPrimaryLanguage(): LanguageInterface
    {
        return $this->primaryLang;
    }

    public function isPrimaryLanguage(LanguageInterface $lang): bool
    {
        return $lang->getIsoCode() === $this->primaryLang->getIsoCode();
    }

    public function getAllowedLanguagesIsoCodes(): array
    {
        return $this->languagesIsoCodes;
    }

    /**
     * @return LanguageInterface[]
     */
    public function getAllowedLanguages(): array
    {
        return $this->languages;
    }

    public function getLanguageLocale(string $lang): string
    {
        return $this->getLanguageByIsoCode($lang)->getLocale();
    }

    public function getLanguageByIsoCode(string $isoCode): LanguageInterface
    {
        $lang = $this->languages[$isoCode] ?? null;

        if (!$lang) {
            throw new LogicException(sprintf('Unknown language "%s"', $isoCode));
        }

        return $lang;
    }

    /**
     * Translate any key (with plural forms and placeholders)
     *
     * @param \BetaKiller\Model\LanguageInterface                                                 $lang
     * @param string|\BetaKiller\Model\HasI18nKeyNameInterface|\BetaKiller\Model\I18nKeyInterface $key
     * @param array|null                                                                          $values
     * @param bool|null                                                                           $ignoreMissing
     *
     * @return string
     * @throws \BetaKiller\I18n\I18nException
     */
    public function translate(
        LanguageInterface $lang,
        string|HasI18nKeyNameInterface|I18nKeyInterface $key,
        ?array $values = null,
        ?bool $ignoreMissing = null
    ): string {
        $key = match (true) {
            is_string($key) => $this->getKeyByName($key),
            $key instanceof I18nKeyInterface => $key, // More specific class first
            $key instanceof HasI18nKeyNameInterface => $this->getKeyByName($key->getI18nKeyName()),
        };

        // Translate key first
        $str = $this->getKeyValue($key, $lang, false, $ignoreMissing);

        if ($this->formatter->isFormatted($str)) {
            $form = $values ? reset($values) : null;

            if (!is_int($form)) {
                throw new I18nException('Plural form can not be detected for key ":key" with values ":values"', [
                    ':key'    => $key->getI18nKeyName(),
                    ':values' => json_encode($values),
                ]);
            }

            $str = $this->pluralize($lang, $str, $form);
        }

        return $this->replacePlaceholders($str, $values);
    }

    private function getKeyByName(string $name): I18nKeyInterface
    {
        if (!self::isI18nKey($name)) {
            throw new I18nException('String ":value" is not an i18 key', [':value' => $name]);
        }

        $keys = $this->getAllTranslationKeys();

        if (!isset($keys[$name])) {
            throw new I18nException('Missing i18n key ":name"', [':name' => $name]);
        }

        return $keys[$name];
    }

    /**
     * @param string $locale
     *
     * @return string[]
     * @throws \Punic\Exception
     */
    public function getLanguagePluralForms(LanguageInterface $lang): array
    {
        return Plural::getRules($lang->getLocale());
    }

    public function validatePluralBag(PluralBagInterface $bag, LanguageInterface $lang): void
    {
        $forms = $this->getLanguagePluralForms($lang);

        foreach ($bag->getAll() as $itemForm => $formValue) {
            if (!in_array($itemForm, $forms, true)) {
                throw new I18nException('Unknown form ":form" for language ":lang"', [
                    ':form' => $itemForm,
                    ':lang' => $lang->getIsoCode(),
                ]);
            }
        }
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
            if (!str_starts_with($key, self::PLACEHOLDER_PREFIX)) {
                $key = self::PLACEHOLDER_PREFIX.$key;
            }

            $output[$key] = $value;
        }

        return $output;
    }

    /**
     * @return I18nKeyInterface[]
     */
    public function getAllTranslationKeys(): array
    {
        if (!$this->keysCache) {
            foreach ($this->loader->loadI18nKeys() as $item) {
                $this->keysCache[$item->getI18nKeyName()] = $item;
            }
        }

        return $this->keysCache;
    }

    /**
     * @param string                              $keyName
     * @param \BetaKiller\Model\LanguageInterface $lang
     */
    public function registerMissingKey(string $keyName, LanguageInterface $lang): void
    {
        $e = new I18nException('Missing translation for key ":key" in lang ":lang"', [
            ':key'  => $keyName,
            ':lang' => $lang->getIsoCode(),
        ]);

        // Store exception with the original key as missing
        LoggerHelper::logRawException($this->logger, $e);
    }

    /**
     * Returns translation of a key. If no translation exists, the empty
     * string will be returned. No parameters are replaced.
     *
     * @param \BetaKiller\Model\I18nKeyInterface  $key text to translate
     * @param \BetaKiller\Model\LanguageInterface $lang
     * @param bool|null                           $any Use any available translation as backup
     * @param bool|null                           $ignoreMissing
     *
     * @return  string
     */
    private function getKeyValue(
        I18nKeyInterface $key,
        LanguageInterface $lang,
        bool $any = null,
        bool $ignoreMissing = null
    ): string {
        if ($any) {
            $value = $key->getI18nValueOrAny($lang);
        } else {
            $value = $key->hasI18nValue($lang) ? $key->getI18nValue($lang) : null;
        }

        if (!$value && $this->fallbackLang) {
            $value = $key->hasI18nValue($this->fallbackLang) ? $key->getI18nValue($this->fallbackLang) : null;
        }

        if (!$value) {
            if (!$ignoreMissing) {
                // Translated string does not exist
                $this->registerMissingKey($key->getI18nKeyName(), $lang);
            }

            // Empty string instead of a key
            return '';
        }

        return $value;
    }

    private function replacePlaceholders(string $string, ?array $values): string
    {
        if (empty($values)) {
            return $string;
        }

        return strtr($string, array_filter($values, 'is_scalar'));
    }

    private function pluralize(LanguageInterface $lang, string $packedString, int $number): string
    {
        $form = Plural::getRuleOfType($number, Plural::RULETYPE_CARDINAL, $lang->getLocale());

        return $this->formatter->parse($packedString)->getValue($form);
    }
}
