<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\AbstractI18nModel;
use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\I18nKeyModelInterface;
use BetaKiller\Model\I18nModelInterface;
use BetaKiller\Model\LanguageInterface;

abstract class AbstractI18nRepository extends AbstractOrmBasedDispatchableRepository implements I18nRepositoryInterface
{
    /**
     * @param \BetaKiller\Model\I18nKeyModelInterface $key
     * @param \BetaKiller\Model\LanguageInterface     $lang
     *
     * @return \BetaKiller\Model\I18nModelInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findItem(I18nKeyModelInterface $key, LanguageInterface $lang): ?I18nModelInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterKey($orm, $key)
            ->filterLanguage($orm, $lang)
            ->findOne($orm);
    }

    /**
     * @param \BetaKiller\Model\I18nKeyModelInterface $key
     * @param array                                   $languagesModels
     *
     * @return \BetaKiller\Model\I18nModelInterface[]
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findItemsByLanguages(I18nKeyModelInterface $key, array $languagesModels): array
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterKey($orm, $key)
            ->filterLanguages($orm, $languagesModels)
            ->findAll($orm);
    }

    /**
     * @param \BetaKiller\Model\LanguageInterface $lang
     *
     * @return \BetaKiller\Model\I18nModelInterface[]
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findItemsByLanguage(LanguageInterface $lang): array
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterLanguage($orm, $lang)
            ->findAll($orm);
    }

    /**
     * @param \BetaKiller\Model\I18nKeyModelInterface $key
     *
     * @return \BetaKiller\Model\I18nModelInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findFirstNoEmpty(I18nKeyModelInterface $key): ?I18nModelInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterKey($orm, $key)
            ->filterNotEmpty($orm)
            ->findOne($orm);
    }

    /**
     * @param \BetaKiller\Model\LanguageInterface[] $languages
     *
     * @return \BetaKiller\Model\I18nModelInterface[]
     */
    public function findEmptyItems(array $languages): array
    {
        $orm = $this->getOrmInstance();

        $orm->having(\DB::expr('COUNT(*)'), '<', \count($languages));

        return $this
            ->filterLanguages($orm, $languages)
            ->groupByKey($orm)
            ->findAll($orm);
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface  $orm
     * @param \BetaKiller\Model\I18nKeyModelInterface $key
     *
     * @return \BetaKiller\Repository\AbstractI18nRepository
     */
    protected function filterKey(ExtendedOrmInterface $orm, I18nKeyModelInterface $key): self
    {
        $orm->where($this->getI18nKeyForeignKey(), '=', $key->getID());

        return $this;
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     * @param \BetaKiller\Model\LanguageInterface    $languageModel
     *
     * @return \BetaKiller\Repository\AbstractI18nRepository
     */
    protected function filterLanguage(ExtendedOrmInterface $orm, LanguageInterface $languageModel): self
    {
        $orm->where($this->getLanguageColumnName(), '=', $languageModel->getID());

        return $this;
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     * @param LanguageInterface[]                    $languagesModels
     *
     * @return \BetaKiller\Repository\AbstractI18nRepository
     */
    protected function filterLanguages(ExtendedOrmInterface $orm, array $languagesModels): self
    {
        $ids = array_map(function (LanguageInterface $model) {
            return $model->getID();
        }, $languagesModels);

        $orm->where($this->getLanguageColumnName(), 'IN', $ids);

        return $this;
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     *
     * @return \BetaKiller\Repository\AbstractI18nRepository
     */
    protected function filterEmpty(ExtendedOrmInterface $orm): self
    {
        $filter = sprintf('CHAR_LENGTH(%s)', AbstractI18nModel::TABLE_FIELD_VALUE);
        $orm->where(\DB::expr($filter), '=', 0);

        return $this;
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     *
     * @return \BetaKiller\Repository\AbstractI18nRepository
     */
    protected function filterNotEmpty(ExtendedOrmInterface $orm): self
    {
        $filter = sprintf('CHAR_LENGTH(%s)', AbstractI18nModel::TABLE_FIELD_VALUE);
        $orm->where(\DB::expr($filter), '>', 0);

        return $this;
    }

    protected function groupByKey(ExtendedOrmInterface $orm): self
    {
        $orm->group_by($orm->object_column($this->getI18nKeyForeignKey()));

        return $this;
    }

    /**
     * @return string
     */
    abstract protected function getLanguageColumnName(): string;

    /**
     * @return string
     */
    abstract protected function getI18nKeyForeignKey(): string;

    /**
     * @return string
     */
    abstract protected function getI18nKeyRelationName(): string;
}
