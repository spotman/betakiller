<?php

declare(strict_types=1);

namespace BetaKiller\Model;

trait ArgumentParameterModelTrait
{
    public static function getParameterName(): string
    {
        return static::getModelName();
    }
}
