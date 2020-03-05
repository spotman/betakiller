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

    // TODO Remove underscore and replace all i18n keys
    private const KEY_REGEX = '/^[a-z0-9_-]+(?:[\.]{1}[a-z0-9-_+]+)+$/m';

    /**
     * @var \BetaKiller\Model\LanguageInterface[]
     */
    private $languages;

    /**
     * @var string[]
     */
    private $languagesIsoCodes;

    /**
     * @var LanguageInterface
     */
    private $primaryLang;

    /**
     * @var LanguageInterface
     */
    private $fallbackLang;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \BetaKiller\Repository\LanguageRepositoryInterface
     */
    private $langRepo;

    /**
     * @var \BetaKiller\I18n\PluralBagFormatterInterface
     */
    private $formatter;

    /**
     * @var \BetaKiller\I18n\I18nKeysLoaderInterface
     */
    private $loader;

    /**
     * @var I18nKeyInterface[]
     */
    private $keysCache = [];

    /**
     * @var \BetaKiller\I18n\I18nConfig
     */
    private $config;

    /**
     * I18nFacade constructor.
     *
     * @param \BetaKiller\Repository\LanguageRepositoryInterface $langRepo
     * @param \BetaKiller\I18n\PluralBagFormatterInterface       $formatter
     * @param \BetaKiller\I18n\I18nKeysLoaderInterface           $loader
     * @param \BetaKiller\I18n\I18nConfig                        $config
     * @param \Psr\Log\LoggerInterface                           $logger
     */
    public function __construct(
        LanguageRepositoryInterface $langRepo,
        PluralBagFormatterInterface $formatter,
        I18nKeysLoaderInterface $loader,
        I18nConfig $config,
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
        $fallbackIsoCode    = $this->config->getFallbackLanguage();
        $this->fallbackLang = $this->languages[$fallbackIsoCode];
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
     * Raw translation without placeholders and plural forms
     *
     * @param \BetaKiller\Model\LanguageInterface       $lang
     * @param \BetaKiller\Model\HasI18nKeyNameInterface $hasKey
     * @param array|null                                $values
     * @param bool|null                                 $ignoreMissing
     *
     * @return string
     * @throws \BetaKiller\I18n\I18nException
     */
    public function translateHasKeyName(
        LanguageInterface $lang,
        HasI18nKeyNameInterface $hasKey,
        ?array $values = null,
        ?bool $ignoreMissing = null
    ): string {
        return $this->translateKeyName($lang, $hasKey->getI18nKeyName(), $values, $ignoreMissing);
    }

    /**
     * @param \BetaKiller\Model\LanguageInterface $lang
     * @param string                              $keyName
     * @param array|null                          $values
     * @param bool|null                           $ignoreMissing
     *
     * @return string
     * @throws \BetaKiller\I18n\I18nException
     */
    public function translateKeyName(
        LanguageInterface $lang,
        string $keyName,
        ?array $values = null,
        ?bool $ignoreMissing = null
    ): string {
        $key = $this->getKeyByName($keyName);

        $string = $this->translate($key, $lang, false, $ignoreMissing);

        return $this->replacePlaceholders($string, $values);
    }

    /**
     * @param \BetaKiller\Model\LanguageInterface $lang
     * @param \BetaKiller\Model\I18nKeyInterface  $key
     * @param array|null                          $values
     * @param bool|null                           $ignoreMissing
     *
     * @return string
     */
    public function translateKey(
        LanguageInterface $lang,
        I18nKeyInterface $key,
        ?array $values = null,
        ?bool $ignoreMissing = null
    ): string {
        $string = $this->translate($key, $lang, false, $ignoreMissing);

        return $this->replacePlaceholders($string, $values);
    }

    /**
     * @param \BetaKiller\Model\LanguageInterface $lang
     * @param \BetaKiller\Model\I18nKeyInterface  $key
     * @param array|null                          $values
     * @param bool|null                           $ignoreMissing
     *
     * @return string
     */
    public function translateKeyAny(
        LanguageInterface $lang,
        I18nKeyInterface $key,
        ?array $values = null,
        ?bool $ignoreMissing = null
    ): string {
        $string = $this->translate($key, $lang, true, $ignoreMissing);

        return $this->replacePlaceholders($string, $values);
    }

    /**
     * Translate provided key to all app languages
     *
     * @param \BetaKiller\Model\I18nKeyInterface $key
     * @param array|null                         $values
     * @param bool|null                          $ignoreMissing
     *
     * @return string[]
     */
    public function translateKeyAll(I18nKeyInterface $key, array $values = null, bool $ignoreMissing = null): array
    {
        $data = [];

        foreach ($this->languages as $lang) {
            $data[$lang->getIsoCode()] = $this->translateKey($lang, $key, $values, $ignoreMissing);
        }

        // Add placeholder for primary lang
        $data[self::PRIMARY_LANG_ISO] = $data[$this->primaryLang->getIsoCode()];

        return $data;
    }

    public function translateHasKeyNameAll(
        HasI18nKeyNameInterface $key,
        array $values = null,
        bool $ignoreMissing = null
    ): array {
        $data = [];

        foreach ($this->languages as $lang) {
            $data[$lang->getIsoCode()] = $this->translateHasKeyName($lang, $key, $values, $ignoreMissing);
        }

        // Add placeholder for primary lang
        $data[self::PRIMARY_LANG_ISO] = $data[$this->primaryLang->getIsoCode()];

        return $data;
    }

    /**
     * @param \BetaKiller\Model\LanguageInterface $lang
     * @param string                              $keyName
     * @param                                     $form
     * @param array|null                          $values
     * @param bool|null                           $ignoreMissing
     *
     * @return string
     * @throws \BetaKiller\I18n\I18nException
     * @throws \Punic\Exception\BadArgumentType
     * @throws \Punic\Exception\ValueNotInList
     */
    public function pluralizeKeyName(
        LanguageInterface $lang,
        string $keyName,
        $form,
        array $values = null,
        ?bool $ignoreMissing = null
    ): string {
        $key = $this->getKeyByName($keyName);

        $string = $this->translate($key, $lang, false, $ignoreMissing);

        $string = $this->pluralize($lang, $string, $form);

        return $this->replacePlaceholders($string, $values);
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

    public function pluralizeKey(string $langName, I18nKeyInterface $key, $form, array $values = null): string
    {
        $lang = $this->getLanguageByIsoCode($langName);

        // Translate key first
        $packedString = $this->translate($key, $lang);

        $string = $this->pluralize($lang, $packedString, $form);

        return $this->replacePlaceholders($string, $values);
    }

    /**
     * @param string $locale
     *
     * @return string[]
     * @throws \Punic\Exception
     */
    public function getPluralFormsForLocale(string $locale): array
    {
        return Plural::getRules($locale);
    }

    public function validatePluralBag(PluralBagInterface $bag, LanguageInterface $lang): void
    {
        $forms = $this->getPluralFormsForLocale($lang->getLocale());

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
            if (strpos($key, self::PLACEHOLDER_PREFIX) !== 0) {
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
        LoggerHelper::logException($this->logger, $e);
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
    private function translate(
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

        if (!$value) {
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

//        if ($values) {
//            // Add prefix if does not exists
//            $values = self::addPlaceholderPrefixToKeys($values);
//        }

        return strtr($string, array_filter($values, 'is_scalar'));
    }

    private function pluralize(LanguageInterface $lang, string $packedString, $form): string
    {
        // Detect form name if a $form is an integer-like
        if ((string)(int)$form === (string)$form) {
            // Detect form based on a count
            $form = Plural::getRuleOfType($form, Plural::RULETYPE_CARDINAL, $lang->getLocale());
        }

        if (!$this->formatter->isFormatted($packedString)) {
            throw new I18nException('Translation in locale ":locale" is not plural but ":value"', [
                ':value'  => $packedString,
                ':locale' => $lang->getLocale(),
            ]);
        }

        // Pluralize next
        return $this->formatter->parse($packedString)->getValue($form);
    }
}
