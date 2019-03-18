<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface I18nKeyInterface extends HasI18nKeyNameInterface
{
    /**
     * @return bool
     */
    public function isPlural(): bool;

    /**
     * Returns i18n value for selected language
     *
     * @param \BetaKiller\Model\LanguageInterface $lang
     *
     * @return string
     */
    public function getI18nValue(LanguageInterface $lang): string;

    /**
     * Returns i18n value for selected language or value for any language
     *
     * @param \BetaKiller\Model\LanguageInterface $lang
     *
     * @return string
     */
    public function getI18nValueOrAny(LanguageInterface $lang): string;

    /**
     * @param \BetaKiller\Model\LanguageInterface $lang
     *
     * @return bool
     */
    public function hasI18nValue(LanguageInterface $lang): bool;

    /**
     * Stores i18n value for selected language
     *
     * @param \BetaKiller\Model\LanguageInterface $lang
     * @param string                              $value
     */
    public function setI18nValue(LanguageInterface $lang, string $value): void;

    /**
     * Returns first not empty i18n value
     *
     * @return string
     */
    public function getAnyI18nValue(): string;

    /**
     * @return bool
     */
    public function hasAnyI18nValue(): bool;
}
