<?php
declare(strict_types=1);

namespace BetaKiller\I18n;

use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Model\LanguageInterface;
use BetaKiller\Repository\LanguageRepositoryInterface;
use Psr\Log\LoggerInterface;

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
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \BetaKiller\I18n\TranslatorInterface
     */
    private $translator;

    /**
     * @var \BetaKiller\Repository\LanguageRepositoryInterface
     */
    private $langRepo;

    public function __construct(
        TranslatorInterface $translator,
        LanguageRepositoryInterface $langRepo,
        LoggerInterface $logger
    ) {
        $this->translator = $translator;
        $this->langRepo   = $langRepo;
        $this->logger     = $logger;

        $this->init();
    }

    private function init(): void
    {
        $this->languages = $this->langRepo->getAllSystem();

        if (!$this->languages) {
            throw new \RuntimeException('Define languages first and import them via import:languages task');
        }

        $this->languagesNames = \array_map(function (LanguageInterface $lang) {
            return $lang->getName();
        }, $this->languages);

        // Set default language locale as a fallback
        $defaultLang   = $this->getDefaultLanguageName();
        $defaultLocale = $this->getLanguageLocale($defaultLang);
        $this->translator->setFallbackLocale($defaultLocale);
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
        foreach ($this->languages as $model) {
            if ($model->getName() === $lang) {
                return $model->getLocale();
            }
        }

        throw new \LogicException(sprintf('Unknown language "%s"', $lang));
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
     * @param string $locale
     *
     * @return string[]
     * @throws \Punic\Exception
     */
    public function getPluralFormsForLocale(string $locale): array
    {
        return \Punic\Plural::getRules($locale);
    }

    public function validatePluralBag(PluralBagInterface $bag, LanguageInterface $lang): void
    {
        $forms = $this->getPluralFormsForLocale($lang->getLocale());

        foreach ($bag->getAll() as $itemForm => $formValue) {
            if (!\in_array($itemForm, $forms, true)) {
                throw new I18nException('Unknown form ":form" for language ":lang"', [
                    ':form' => $itemForm,
                    ':lang' => $lang->getName(),
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
