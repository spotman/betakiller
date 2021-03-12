<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\I18nKeyModelInterface;
use BetaKiller\Model\LanguageInterface;
use BetaKiller\Utils\Kohana\ORM\OrmQueryBuilderInterface;
use DB;

abstract class AbstractI18nKeyRepository extends AbstractOrmBasedDispatchableRepository implements
    I18nKeyRepositoryInterface
{
    protected const SEARCH_EXACT    = 'exact';
    protected const SEARCH_STARTING = 'starting';
    protected const SEARCH_CONTAINS = 'contains';
    protected const SEARCH_SOUNDEX  = 'soundex';

    /**
     * @param string                                   $value
     *
     * @param \BetaKiller\Model\LanguageInterface|null $lang
     * @param string|null                              $mode
     *
     * @return \BetaKiller\Model\I18nKeyModelInterface|null
     */
    public function findByI18nValue(
        string $value,
        LanguageInterface $lang = null,
        string $mode = null
    ): ?I18nKeyModelInterface {
        $orm = $this->getOrmInstance();

        return $this
            ->filterI18nValue($orm, $value, $lang, $mode ?? self::SEARCH_EXACT)
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
            return $left->getI18nValueOrAny($lang) <=> $right->getI18nValueOrAny($lang);
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

        // Remove non-alphanumeric symbols
        // @see https://stackoverflow.com/questions/8347655/regex-to-remove-non-alphanumeric-characters-from-utf8-strings#comment89304232_8347715
        $term = \preg_replace('/[^\pL\pN\pS\pP\s]+/u', '', $term);

        $column = $this->getI18nValuesColumnName($orm);

        switch ($mode) {
            case self::SEARCH_STARTING:
            case self::SEARCH_CONTAINS:
            case self::SEARCH_EXACT:
                return $this->filterWithRegex($orm, $column, $term, $mode, $lang);

            case self::SEARCH_SOUNDEX:
                return $this->filterWithSoundex($orm, $column, $term);

            default:
                throw new RepositoryException('Unknown i18n search mode ":mode"', [
                    ':mode' => $mode,
                ]);
        }
    }

    private function filterWithRegex(
        OrmQueryBuilderInterface $orm,
        string $col,
        string $term,
        string $mode,
        ?LanguageInterface $lang
    ): self {
        $utfRegex = $this->makeI18nFilterRegex($term, $mode);

        if ($lang) {
            $utfRegex = sprintf('"%s"', $lang->getIsoCode()).$utfRegex;
        }

//        $columnExpr = DB::expr(sprintf(
////            'REGEXP_LIKE(%s COLLATE utf8mb4_unicode_ci, \'%s\' COLLATE utf8mb4_unicode_ci)',
//            '%s COLLATE utf8mb4_unicode_ci REGEXP \'%s\' COLLATE utf8mb4_unicode_ci',
//            $col,
//            $utfRegex
//        ));
//        $orm->and_where($columnExpr, '=', 1);

        // Case insensitive match for different encodings via COLLATE
        $orm->and_where(
            DB::expr(sprintf('%s COLLATE utf8mb4_unicode_ci', $col)),
            'REGEXP',
            DB::expr(sprintf('\'%s\' COLLATE utf8mb4_unicode_ci', $utfRegex))
        );

        return $this;
    }

    private function makeI18nFilterRegex(string $term, string $mode): string
    {
        // I18n data is stored in JSON columns so apply JSON escaping strategy and remove quotes
        $term = trim(json_encode($term, \JSON_THROW_ON_ERROR), '"');

        // Double slash for special characters
        // @see https://dev.mysql.com/doc/refman/8.0/en/regexp.html#regexp-syntax
        $term = \str_replace(
            [
                '+',
                '#',
                '&',
                '/',
                '\\',
            ],
            [
                '\\+',
                '\\#',
                '\\&',
                '\\/',
                '\\\\',
            ],
            $term
        );

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
