<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\Language;
use BetaKiller\Model\LanguageInterface;

/**
 * @method LanguageInterface[] getAll()
 */
class LanguageRepository extends AbstractOrmBasedRepository implements LanguageRepositoryInterface
{
    /**
     * @param string $name
     *
     * @return \BetaKiller\Model\LanguageInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findByName(string $name): ?LanguageInterface
    {
        $orm = $this->getOrmInstance();
        $this->filterByName($orm, $name);

        return $this->findOne($orm);
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     * @param string                                 $name
     */
    protected function filterByName(ExtendedOrmInterface $orm, string $name): void
    {
        $orm->where(Language::TABLE_FIELD_NAME, '=', $name);
    }
}
