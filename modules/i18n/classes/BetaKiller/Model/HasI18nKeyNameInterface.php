<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface HasI18nKeyNameInterface
{
    /**
     * Returns name of I18n key to proceed
     *
     * @return string
     */
    public function getI18nKeyName(): string;
}
