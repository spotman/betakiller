<?php

declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Url\UrlPrototype;

trait DispatchableByIdTrait
{
    public function getUrlKeyName(): string
    {
        return UrlPrototype::KEY_ID;
    }
}
