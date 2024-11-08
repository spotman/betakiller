<?php

declare(strict_types=1);

namespace BetaKiller\Url;

enum Zone: string implements ZoneInterface
{
    case Public = 'public';
    case Personal = 'personal';
    case Preview = 'preview';
    case Admin = 'admin';
    case Developer = 'developer';

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->value;
    }
}
