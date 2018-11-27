<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface TranslationKeyModelInterface extends I18nKeyModelInterface
{
    /**
     * @param string $keyName
     */
    public function setI18nKey(string $keyName): void;

    public function markAsPlural(): void;

    public function markAsRegular(): void;
}
