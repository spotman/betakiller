<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\I18nKeyInterface;

abstract class AbstractI18nKeyRepository extends AbstractOrmBasedDispatchableRepository implements
    I18nKeyRepositoryInterface
{
    /**
     * @param array $langModels
     *
     * @return I18nKeyInterface[]|mixed[]
     */
    public function findKeysWithEmptyValues(array $langModels): array
    {
        $orm = $this->getOrmInstance();

        $column = $orm->object_column($this->getValuesColumnName());

        $orm->and_where_open();
        foreach ($langModels as $lang) {
            $orm->or_where($column, 'NOT LIKE', '%"'.$lang->getName().'"%');
        }
        $orm->and_where_close();

        return $this->findAll($orm);
    }

    abstract protected function getValuesColumnName(): string;
}
