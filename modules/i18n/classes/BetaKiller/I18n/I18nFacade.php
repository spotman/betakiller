<?php
declare(strict_types=1);

namespace BetaKiller\I18n;

use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Model\HasI18nKeyNameInterface;
use BetaKiller\Model\I18nKeyInterface;
use BetaKiller\Model\LanguageInterface;
use BetaKiller\Repository\LanguageRepositoryInterface;
use Psr\Log\LoggerInterface;
use Punic\Plural;

final class I18nFacade
{
    use LoggerHelperTrait;

    public const ROLE_TRANSLATOR = 'translator';

    public const PLACEHOLDER_PREFIX = ':';

    private const KEY_REGEX = '/^[a-z0-9_]+(?:[\.]{1}[a-z0-9-_]+)+$/m';

    /**
     * @var \BetaKiller\Model\LanguageInterface[]
     */
    private $languages;

    /**
     * @var string[]
     */
    private $languagesNames;

    /**
     * @var LanguageInterface
     */
    private $defaultLang;

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

    public function __construct(
        LanguageRepositoryInterface $langRepo,
        PluralBagFormatterInterface $formatter,
        I18nKeysLoaderInterface $loader,
        LoggerInterface $logger
    ) {
        $this->langRepo  = $langRepo;
        $this->loader    = $loader;
        $this->formatter = $formatter;
        $this->logger    = $logger;

        $this->init();
    }

    private function init(): void
    {
        $this->languages = $this->langRepo->getAppLanguages();

        if (!$this->languages) {
            throw new \RuntimeException('Define languages first and import them via import:languages task');
        }

        $this->languagesNames = \array_map(function (LanguageInterface $lang) {
            return $lang->getIsoCode();
        }, $this->languages);

        // Set default language locale as a fallback
        $this->defaultLang = $this->getLanguageByName($this->getDefaultLanguageName());
    }

    public function hasLanguage(string $lang): bool
    {
        return \in_array($lang, $this->languagesNames, true);
    }

    public function getDefaultLanguageName(): string
    {
        // First language is primary
        return $this->languagesNames[0];
    }

    public function getAllowedLanguagesNames(): array
    {
        return $this->languagesNames;
    }

    public function getLanguageLocale(string $lang): string
    {
        return $this->getLanguageByName($lang)->getLocale();
    }

    public function getLanguageByName(string $lang): LanguageInterface
    {
        foreach ($this->languages as $model) {
            if ($model->getIsoCode() === $lang) {
                return $model;
            }
        }

        throw new \LogicException(sprintf('Unknown language "%s"', $lang));
    }

    /**
     * Raw translation without placeholders and plural forms
     *
     * @param string                                    $langName
     * @param \BetaKiller\Model\HasI18nKeyNameInterface $hasKey
     *
     * @return string
     * @throws \BetaKiller\I18n\I18nException
     */
    public function translateHasKeyName(string $langName, HasI18nKeyNameInterface $hasKey): string
    {
        return $this->translateKeyName($langName, $hasKey->getI18nKeyName());
    }

    public function translateKeyName(string $langName, string $keyName, array $values = null): string
    {
        $key  = $this->getKeyByName($keyName);
        $lang = $this->getLanguageByName($langName);

        $string = $this->translate($key, $lang);

        return $this->replacePlaceholders($string, $values);
    }

    public function translateKey(string $langName, I18nKeyInterface $key, array $values = null): string
    {
        $lang = $this->getLanguageByName($langName);

        $string = $this->translate($key, $lang);

        return $this->replacePlaceholders($string, $values);
    }

    public function pluralizeKeyName(string $langName, string $keyName, $form, array $values = null): string
    {
        $key  = $this->getKeyByName($keyName);
        $lang = $this->getLanguageByName($langName);

        $string = $this->translate($key, $lang);

        $string = $this->pluralize($lang, $string, $form);

        return $this->replacePlaceholders($string, $values);
    }

    private function getKeyByName(string $name): I18nKeyInterface
    {
        if (!self::isI18nKey($name)) {
            throw new I18nException('String ":value" is not an i18 key', [':value' => $name]);
        }

        if (!$this->keysCache) {
            foreach ($this->loader->loadI18nKeys() as $item) {
                $this->keysCache[$item->getI18nKeyName()] = $item;
            }
        }

        if (!isset($this->keysCache[$name])) {
            throw new I18nException('Missing i18n key ":name"', [':name' => $name]);
        }

        return $this->keysCache[$name];
    }

    public function pluralizeKey(string $langName, I18nKeyInterface $key, $form, array $values = null): string
    {
        $lang = $this->getLanguageByName($langName);

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
            if (!\in_array($itemForm, $forms, true)) {
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

    /**
     * Returns translation of a key. If no translation exists, the empty
     * string will be returned. No parameters are replaced.
     *
     * @param \BetaKiller\Model\I18nKeyInterface  $key text to translate
     * @param \BetaKiller\Model\LanguageInterface $lang
     *
     * @return  string
     */
    private function translate(I18nKeyInterface $key, LanguageInterface $lang): string
    {
        try {
            $value = $key->getI18nValue($lang);

            if (!$value) {
                $value = $key->getI18nValue($this->defaultLang);
            }

            if (!$value) {
                throw new I18nException('Missing translation for key ":key" in locale ":locale"', [
                    ':key'    => $key->getI18nKeyName(),
                    ':locale' => $lang->getLocale(),
                ]);
            }

            return $value;
        } catch (I18nException $e) {
            // Translated string does not exist
            // Store exception with the original key as missing
            $this->logException($this->logger, $e);

            // Empty string instead of a key
            return '';
        }
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
