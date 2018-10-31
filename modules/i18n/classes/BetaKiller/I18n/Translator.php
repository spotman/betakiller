<?php
declare(strict_types=1);

namespace BetaKiller\I18n;

class Translator implements TranslatorInterface
{
    /**
     * @var \BetaKiller\I18n\LoaderInterface
     */
    private $loader;

    /**
     * @var \BetaKiller\I18n\PluralBagFormatterInterface
     */
    private $formatter;

    /**
     * @var string
     */
    private $fallbackLocale;

    /**
     * Translator constructor.
     *
     * @param \BetaKiller\I18n\LoaderInterface             $loader
     * @param \BetaKiller\I18n\PluralBagFormatterInterface $formatter
     */
    public function __construct(LoaderInterface $loader, PluralBagFormatterInterface $formatter)
    {
        $this->loader    = $loader;
        $this->formatter = $formatter;
    }

    public function translate(string $key, string $locale): string
    {
        $value = $this->getValue($key, $locale);

        if (!$value && $this->fallbackLocale) {
            $value = $this->getValue($key, $this->fallbackLocale);
        }

        if (!$value) {
            throw new I18nException('Missing translation for key ":key" in locale ":locale"', [
                ':key'    => $key,
                ':locale' => $locale,
            ]);
        }

        return $value;
    }

    public function pluralize(string $key, string $form, string $locale): string
    {
        $packedString = $this->translate($key, $locale);

        return $this->formatter->parse($packedString)->getValue($form);
    }

    private function getValue(string $key, string $locale): ?string
    {
        $records = $this->loader->load($locale);

        return $records[$key] ?? null;
    }

    /**
     * @param string $locale
     */
    public function setFallbackLocale(string $locale): void
    {
        $this->fallbackLocale = $locale;
    }
}
