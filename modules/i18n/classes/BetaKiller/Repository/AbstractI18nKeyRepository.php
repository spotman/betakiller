<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\I18nKeyModelInterface;

abstract class AbstractI18nKeyRepository extends AbstractOrmBasedDispatchableRepository implements
    I18nKeyRepositoryInterface
{
    public function findByKeyName(string $i18nKey): ?I18nKeyModelInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterKey($orm, $i18nKey)
            ->findOne($orm);
    }

    private function filterKey(ExtendedOrmInterface $orm, string $key): self
    {
        $orm->where($orm->object_column($this->getKeyFieldName()), '=', $key);

        return $this;
    }

    abstract protected function getKeyFieldName(): string;
}
