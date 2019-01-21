<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Helper\TextHelper;
use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\LanguageInterface;

abstract class AbstractI18nKeyRepository extends AbstractOrmBasedDispatchableRepository implements
    I18nKeyRepositoryInterface
{
    protected const SEARCH_EXACT    = 'exact';
    protected const SEARCH_STARTING = 'starting';
    protected const SEARCH_WEAK     = 'weak';

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

    protected function filterI18nValue(
        ExtendedOrmInterface $orm,
        string $term,
        LanguageInterface $lang = null,
        string $mode = null
    ) {
        $column = $this->getI18nValuesColumnName($orm);

        $term = \mb_strtolower(TextHelper::utf8ToAscii($term));

        $regex = $this->makeI18nFilterRegex($term, $mode ?? self::SEARCH_STARTING);

        if ($lang) {
            $regex = sprintf('"%s"', $lang->getIsoCode()).$regex;
        }

        $orm->where(\DB::expr('LOWER(CONVERT('.$column.' USING ascii))'), 'REGEXP', $regex);

        return $this;
    }

    private function makeI18nFilterRegex(string $term, string $mode): string
    {
        switch ($mode) {
            case self::SEARCH_EXACT:
                return sprintf(':"%s"', $term);

            case self::SEARCH_STARTING:
                return sprintf(':"%s[^"]*"', $term);

            case self::SEARCH_WEAK:
                return sprintf(':"[^"]*%s[^"]*"', $term);

            default:
                throw new RepositoryException('Unknown i18n filter mode ":mode"', [
                    ':mode' => $mode,
                ]);
        }
    }

    protected function filterLang(ExtendedOrmInterface $orm, LanguageInterface $lang, bool $inverse = null)
    {
        $column = $this->getI18nValuesColumnName($orm);

        $orm->where(
            $column,
            $inverse ? 'NOT LIKE' : 'LIKE',
            '%"'.$lang->getIsoCode().'"%'
        );

        return $this;
    }

    protected function filterEmptyI18n(ExtendedOrmInterface $orm)
    {
        $column = $this->getI18nValuesColumnName($orm);

        $orm->and_where_open();

        $orm->or_where(\DB::expr('LENGTH(:col)', [':col' => $column]), '=', 0);
        $orm->or_where($column, 'IS', null);

        $orm->and_where_close();

        return $this;
    }

    abstract protected function getI18nValuesColumnName(ExtendedOrmInterface $orm): string;
}
