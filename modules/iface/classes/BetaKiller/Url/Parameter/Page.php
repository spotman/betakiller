<?php

namespace BetaKiller\Url\Parameter;

use Webmozart\Assert\Assert;

final readonly class Page extends AbstractIntegerUrlParameter
{
    protected static function getUriPrefix(): string
    {
        return 'page';
    }

    protected static function check(int $value): void
    {
        Assert::greaterThan($value, 0);
    }
}
