<?php
declare(strict_types=1);

namespace BetaKiller\Url;

interface ZoneInterface
{
    public const PERSONAL = 'personal';
    public const PREVIEW  = 'preview';
    public const PUBLIC   = 'public';
    public const ADMIN    = 'admin';

    /**
     * @return string
     */
    public function getName(): string;
}
