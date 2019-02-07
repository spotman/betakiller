<?php
declare(strict_types=1);

namespace BetaKiller\Backup;

class DavBackup extends \DavBackup
{
    public function __construct(string $url, string $login, string $password)
    {
        parent::__construct($url, $login, $password);
    }
}
