<?php

declare(strict_types=1);

namespace BetaKiller\Url\Parameter;

final readonly class NotificationMessageCodename extends AbstractStringUrlParameter
{
    public static function fromUriValue(string $value): static
    {
        $value = str_replace('_', '/', $value);

        return parent::fromUriValue($value);
    }

    public function exportUriValue(): string
    {
        $value = parent::exportUriValue();

        return str_replace('/', '_', $value);
    }
}
