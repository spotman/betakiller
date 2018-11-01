<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\I18nKeyModelInterface;

interface I18nKeyRepositoryInterface extends RepositoryInterface
{
    public function findByKeyName(string $i18nKey): ?I18nKeyModelInterface;
}
