<?php

declare(strict_types=1);

namespace Spotman\Defence\Parameter;

trait ArgumentParameterNameTrait
{
    public static function getParameterName(): string
    {
        $parts = explode('\\', static::class);

        return array_pop($parts);
    }
}
