<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface I18nModelInterface
{
    /**
     * @param \BetaKiller\Model\I18nKeyModelInterface $model
     *
     * @return \BetaKiller\Model\I18nModelInterface
     */
    public function setKey(I18nKeyModelInterface $model): I18nModelInterface;

    /**
     * @return \BetaKiller\Model\I18nKeyModelInterface
     */
    public function getKey(): I18nKeyModelInterface;

    /**
     * @param \BetaKiller\Model\LanguageInterface $languageModel
     *
     * @return \BetaKiller\Model\I18nModelInterface
     */
    public function setLanguage(LanguageInterface $languageModel): I18nModelInterface;

    /**
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function getLanguage(): LanguageInterface;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\I18nModelInterface
     */
    public function setValue(string $value): I18nModelInterface;

    /**
     * @return string
     */
    public function getValue(): string;
}
