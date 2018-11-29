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

        return $this
            ->filterLang($orm, $lang, true)
            ->findAll($orm);
    }

    protected function filterI18nValue(ExtendedOrmInterface $orm, string $term, LanguageInterface $lang = null)
    {
        $column = $orm->object_column($this->getI18nValuesColumnName());

        $regex = $lang
            ? sprintf('"%s":"[^\"]*%s[^\"]*"', $lang->getName(), $term)
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
            '%"'.$lang->getName().'"%'
        );

        return $this;
    }

    abstract protected function getI18nValuesColumnName(): string;
}
