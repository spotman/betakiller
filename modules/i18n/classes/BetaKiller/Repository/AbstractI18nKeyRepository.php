<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
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

        $orm->or_where_open();
        $this->filterLang($orm, $lang, true);
        $orm->or_where_close();

        $orm->or_where_open();
        $this->filterEmptyI18n($orm);
        $orm->or_where_close();

        return $this
            ->findAll($orm);
    }

    /**
     * @return \BetaKiller\Model\I18nKeyModelInterface[]|mixed[]
     */
    public function getAllI18nKeys(): array
    {
        return $this->getAll();
    }

    protected function filterI18nValue(ExtendedOrmInterface $orm, string $term, LanguageInterface $lang = null)
    {
        $column = $orm->object_column($this->getI18nValuesColumnName());

        $regex = $lang
            ? sprintf('"%s":"[^\"]*%s[^\"]*"', $lang->getIsoCode(), $term)
            : sprintf(':"[^\"]*%s', $term);

        $orm->where(\DB::expr('LOWER('.$column.')'), 'REGEXP', $regex);

        return $this;
    }

    protected function filterLang(ExtendedOrmInterface $orm, LanguageInterface $lang, bool $inverse = null)
    {
        $column = $orm->object_column($this->getI18nValuesColumnName());

        $orm->where(
            $column,
            $inverse ? 'NOT LIKE' : 'LIKE',
            '%"'.$lang->getIsoCode().'"%'
        );

        return $this;
    }

    protected function filterEmptyI18n(ExtendedOrmInterface $orm)
    {
        $column = $orm->object_column($this->getI18nValuesColumnName());

        $orm->and_where_open();

        $orm->or_where(\DB::expr('LENGTH(:col)', [':col' => $column]) ,'=',0);
        $orm->or_where($column ,'IS',null);

        $orm->and_where_close();

        return $this;
    }

    abstract protected function getI18nValuesColumnName(): string;
}
