<?php
declare(strict_types=1);

namespace BetaKiller\Model;

trait ImmutableI18nKeyModelTrait
{
    /**
     * @param string $keyName
     */
    public function setI18nKey(string $keyName): void
    {
        throw new \LogicException('Immutable i18n key model');
    }

    public function markAsPlural(): void
    {
        throw new \LogicException('Immutable i18n key model');
    }

    public function markAsRegular(): void
    {
        throw new \LogicException('Immutable i18n key model');
    }
}
