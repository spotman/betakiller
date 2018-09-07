<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface I18nInterface
{
    /**
     * @param \BetaKiller\Model\LanguageInterface $languageModel
     *
     * @return \BetaKiller\Model\I18nInterface
     */
    public function setLanguage(LanguageInterface $languageModel): I18nInterface;

    /**
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function getLanguage(): LanguageInterface;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\I18nInterface
     */
    public function setValue(string $value): I18nInterface;

    /**
     * @return string
     */
    public function getValue(): string;
}
