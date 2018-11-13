<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface I18nKeyModelInterface extends DispatchableEntityInterface
{
    /**
     * @return string
     */
    public function getI18nKey(): string;

    /**
     * @param string $keyName
     */
    public function setI18nKey(string $keyName): void;

    /**
     * @return bool
     */
    public function isPlural(): bool;

    public function markAsPlural(): void;

    public function markAsRegular(): void;
}
