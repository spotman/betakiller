<?php

declare(strict_types=1);

namespace BetaKiller\Session;

enum SessionCause: string
{
    case Absent = 'absent';
    case Auth = 'auth';
    case Regenerated = 'regenerated';
    case Expired = 'expired';
    case Invalid = 'invalid';
    case Transitioned = 'transitioned';
    case Missing = 'missing';
    case Fake = 'fake';
    case Unknown = 'unknown';

    public static function fromCodename(string $value): self
    {
        return self::from($value);
    }

    public function getCodename(): string
    {
        return $this->value;
    }

    public function isValid(): bool
    {
        return in_array($this, [
            self::Auth,
            self::Regenerated,
            self::Transitioned,
        ]);
    }
}
