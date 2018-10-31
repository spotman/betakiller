<?php
declare(strict_types=1);

namespace BetaKiller\I18n;

interface TranslatorInterface
{
    /**
     * @param string $key
     * @param string $locale
     *
     * @return string
     * @throws \BetaKiller\I18n\I18nException
     */
    public function translate(string $key, string $locale): string;

    /**
     * @param string $key
     * @param string $form
     * @param string $locale
     *
     * @return string
     * @throws \BetaKiller\I18n\I18nException
     */
    public function pluralize(string $key, string $form, string $locale): string;

    /**
     * @param string $locale
     */
    public function setFallbackLocale(string $locale): void;
}
