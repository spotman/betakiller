<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\Token;
use BetaKiller\Model\TokenInterface;
use BetaKiller\Model\UserInterface;

/**
 * @method TokenInterface findById(string $id)
 * @method TokenInterface[] getAll()
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
     * @param \BetaKiller\Model\UserInterface $userModel
     * @param string                          $value
     *
     * @return \BetaKiller\Model\TokenInterface|null
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function find(UserInterface $userModel, string $value): ?TokenInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterByValue($orm, $value)
            ->filterByUser($orm, $userModel)
            ->findOne($orm);
    }

    /**
     * @param \BetaKiller\Model\UserInterface $userModel
     * @param string                          $value
     *
     * @return \BetaKiller\Model\TokenInterface|null
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findActive(UserInterface $userModel, string $value): ?TokenInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterByValue($orm, $value)
            ->filterByUser($orm, $userModel)
            ->filterByActive($orm)
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
            ->filterByNotActive($orm)
            ->findAll($orm);
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     * @param string                                 $value
     *
     * @return \BetaKiller\Repository\TokenRepository
     */
    private function filterByValue(ExtendedOrmInterface $orm, string $value): self
    {
        $column = $orm->object_column(Token::TABLE_FIELD_VALUE);
        $orm->where($column, '=', $value);

        return $this;
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     * @param \BetaKiller\Model\UserInterface        $userModel
     *
     * @return \BetaKiller\Repository\TokenRepository
     */
    private function filterByUser(ExtendedOrmInterface $orm, UserInterface $userModel): self
    {
        $column = $orm->object_column(Token::TABLE_FIELD_USER_ID);
        $orm->where($column, '=', $userModel);

        return $this;
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     *
     * @return \BetaKiller\Repository\TokenRepository
     */
    private function filterByActive(ExtendedOrmInterface $orm): self
    {
        $column      = $orm->object_column(Token::TABLE_FIELD_ENDING_AT);
        $currentDate = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $orm->where($column, '>', $currentDate);

        return $this;
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     *
     * @return \BetaKiller\Repository\TokenRepository
     */
    private function filterByNotActive(ExtendedOrmInterface $orm): self
    {
        $column      = $orm->object_column(Token::TABLE_FIELD_ENDING_AT);
        $currentDate = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $orm->where($column, '<=', $currentDate);

        return $this;
    }
}
