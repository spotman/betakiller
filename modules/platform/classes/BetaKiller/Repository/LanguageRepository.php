<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\Language;
use BetaKiller\Model\LanguageInterface;

/**
 * @method LanguageInterface findById(string $id)
 * @method LanguageInterface[] getAll()
 */
final class LanguageRepository extends AbstractOrmBasedRepository implements LanguageRepositoryInterface
{
    public function getByName(string $name): LanguageInterface
    {
        $model = $this->findByName($name);

        if (!$model) {
            throw new RepositoryException('Missing language with name :value', [
                ':value' => $name,
            ]);
        }

        return $model;
    }

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

    public function getByLocale(string $locale): LanguageInterface
    {
        $orm = $this->getOrmInstance();
        $this->filterByLocale($orm, $locale);

        $model = $this->findOne($orm);

        if (!$model) {
            throw new RepositoryException('Missing language with locale :value', [
                ':value' => $locale,
            ]);
        }

        return $model;
    }

    /**
     * @return \BetaKiller\Model\Language[]
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getAllSystem(): array
    {
        $orm = $this->getOrmInstance();
        $this->filterBySystem($orm);

        return $this->findAll($orm);
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     */
    private function filterBySystem(ExtendedOrmInterface $orm): void
    {
        $orm->where(Language::TABLE_FIELD_IS_SYSTEM, '=', 1);
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     * @param string                                 $name
     */
    private function filterByName(ExtendedOrmInterface $orm, string $name): void
    {
        $orm->where(Language::TABLE_FIELD_NAME, '=', $name);
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     * @param string                                 $locale
     */
    private function filterByLocale(ExtendedOrmInterface $orm, string $locale): void
    {
        $orm->where(Language::TABLE_FIELD_LOCALE, '=', $locale);
    }
}
