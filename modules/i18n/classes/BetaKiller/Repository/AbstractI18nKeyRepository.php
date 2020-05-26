<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Helper\TextHelper;
use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\I18nKeyModelInterface;
use BetaKiller\Model\LanguageInterface;
use DB;

abstract class AbstractI18nKeyRepository extends AbstractOrmBasedDispatchableRepository implements
    I18nKeyRepositoryInterface
{
    protected const SEARCH_EXACT    = 'exact';
    protected const SEARCH_STARTING = 'starting';
    protected const SEARCH_CONTAINS = 'weak';

    /**
     * @param string                                   $value
     *
     * @param \BetaKiller\Model\LanguageInterface|null $lang
     *
     * @return \BetaKiller\Model\I18nKeyModelInterface|null
     */
    public function findByI18nValue(string $value, LanguageInterface $lang = null): ?I18nKeyModelInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterI18nValue($orm, $value, $lang, self::SEARCH_EXACT)
            ->findOne($orm);
    }

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

        return $this->findAll($orm);
    }

    /**
     * @return \BetaKiller\Model\I18nKeyModelInterface[]|mixed[]
     */
    public function getAllI18nKeys(): array
    {
        return $this->getAll();
    }

    /**
     * @param \BetaKiller\Model\LanguageInterface $lang
     *
     * @return \BetaKiller\Model\I18nKeyModelInterface[]|mixed[]
     */
    public function getAllOrderedByI18nValue(LanguageInterface $lang): array
    {
        return $this->orderItemsByI18nValue($this->getAll(), $lang);
    }

    protected function orderItemsByI18nValue(array $items, LanguageInterface $lang): array
    {
        usort($items, static function (I18nKeyModelInterface $left, I18nKeyModelInterface $right) use ($lang) {
            return $left->getI18nValue($lang) <=> $right->getI18nValue($lang);
        });

        return $items;
    }

    protected function filterI18nValue(
        ExtendedOrmInterface $orm,
        string $term,
        ?LanguageInterface $lang = null,
        string $mode = null
    ): self {
        $mode = $mode ?? self::SEARCH_STARTING;

        $column = $this->getI18nValuesColumnName($orm);
        $term   = mb_strtolower($term);

        $utfRegex   = $this->makeI18nFilterRegex($term, $mode);
        $asciiRegex = $this->makeI18nFilterRegex(TextHelper::utf8ToAscii($term), $mode);

        if ($lang) {
            $utfRegex   = sprintf('"%s"', $lang->getIsoCode()).$utfRegex;
            $asciiRegex = sprintf('"%s"', $lang->getIsoCode()).$asciiRegex;
        }

        $orm->and_where_open();

        $orm->or_where(DB::expr('LOWER('.$column.')'), 'REGEXP', $utfRegex);
        $orm->or_where(DB::expr('LOWER(CONVERT('.$column.' USING ascii))'), 'REGEXP', $asciiRegex);

        $orm->and_where_close();

        return $this;
    }

    private function makeI18nFilterRegex(string $term, string $mode): string
    {
        $term = preg_quote($term, '/');

        switch ($mode) {
            case self::SEARCH_EXACT:
                return sprintf(':"%s"', $term);

            case self::SEARCH_STARTING:
                return sprintf(':"%s[^"]*"', $term);

            case self::SEARCH_CONTAINS:
                return sprintf(':"[^"]*%s[^"]*"', $term);

            default:
                throw new RepositoryException('Unknown i18n filter mode ":mode"', [
                    ':mode' => $mode,
                ]);
        }
    }

    protected function filterLang(ExtendedOrmInterface $orm, LanguageInterface $lang, bool $inverse = null): self
    {
        $column = $this->getI18nValuesColumnName($orm);

        $orm->where(
            $column,
            $inverse ? 'NOT LIKE' : 'LIKE',
            '%"'.$lang->getIsoCode().'"%'
        );

        return $this;
    }

    protected function filterEmptyI18n(ExtendedOrmInterface $orm): self
    {
        $column = $this->getI18nValuesColumnName($orm);

        $orm->and_where_open();

        $orm->or_where(DB::expr('LENGTH(:col)', [':col' => $column]), '=', 0);
        $orm->or_where($column, 'IS', null);

        $orm->and_where_close();

        return $this;
    }

    abstract protected function getI18nValuesColumnName(ExtendedOrmInterface $orm): string;
}
