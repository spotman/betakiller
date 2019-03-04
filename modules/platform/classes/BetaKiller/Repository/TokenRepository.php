<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\Token;
use BetaKiller\Model\TokenInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

/**
 * @method TokenInterface findById(string $id)
 * @method TokenInterface[] getAll()
 * @method void save(TokenInterface $entity)
 */
class TokenRepository extends AbstractOrmBasedMultipleParentsTreeRepository
{
    /**
     * @return string
     */
    public function getUrlKeyName(): string
    {
        return Token::TABLE_FIELD_VALUE;
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\TokenInterface|null
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findByValue(string $value): ?TokenInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterValue($orm, $value)
            ->findOne($orm);
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\TokenInterface|null
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findActive(string $value): ?TokenInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterValue($orm, $value)
            ->filterActive($orm, true)
            ->findOne($orm);
    }

    /**
     * @return TokenInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findAllNotActive(): array
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterActive($orm, false)
            ->findAll($orm);
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param string                                    $value
     *
     * @return \BetaKiller\Repository\TokenRepository
     */
    private function filterValue(OrmInterface $orm, string $value): self
    {
        $column = $orm->object_column(Token::TABLE_FIELD_VALUE);
        $orm->where($column, '=', $value);

        return $this;
    }

    /**
     * @param OrmInterface $orm
     * @param bool         $active
     *
     * @return \BetaKiller\Repository\TokenRepository
     * @throws \Exception
     */
    private function filterActive(OrmInterface $orm, bool $active): self
    {
        $column = $orm->object_column(Token::TABLE_FIELD_ENDING_AT);
        $now    = new \DateTimeImmutable();

        $orm->filter_datetime_column_value($column, $now, $active ? '>' : '<=');

        return $this;
    }

    private function filterNotUsed(OrmInterface $orm): self
    {
        $column = $orm->object_column(Token::TABLE_FIELD_USED_AT);

        $orm->where($column, 'IS', null);

        return $this;
    }

    protected function customFilterForUrlDispatching(OrmInterface $orm, UrlContainerInterface $params): void
    {
        $this
            ->filterNotUsed($orm)
            ->filterActive($orm, true);
    }
}
