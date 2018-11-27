<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\LanguageInterface;

abstract class AbstractI18nKeyRepository extends AbstractOrmBasedDispatchableRepository implements
    I18nKeyRepositoryInterface
{
    /**
     * @param \BetaKiller\Model\LanguageInterface $lang
     *
     * @return \BetaKiller\Model\I18nKeyModelInterface[]|mixed[]
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findKeysWithEmptyValues(LanguageInterface $lang): array
    {
        $orm = $this->getOrmInstance();

        $column = $orm->object_column($this->getValuesColumnName());

        $orm->where($column, 'NOT LIKE', '%"'.$lang->getName().'"%');

        return $this->findAll($orm);
    }

    abstract protected function getValuesColumnName(): string;
}
