<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\AbstractEntityInterface;

interface RepositoryInterface
{
    public const CLASS_PREFIX = 'Repository';
    public const CLASS_SUFFIX = 'Repository';

    public static function getCodename(): string;

    /**
     * @param string $id
     *
     * @return AbstractEntityInterface|mixed

     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findById(string $id);

    /**
     * @param string $id
     *
     * @return AbstractEntityInterface|mixed
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getById(string $id);

    /**
     * @return \BetaKiller\Model\AbstractEntityInterface[]
     */
    public function getAll(): array;

    /**
     * @param int|null $currentPage
     * @param int|null $itemsPerPage
     *
     * @return \BetaKiller\Model\AbstractEntityInterface[]
     */
    public function getAllPaginated(int $currentPage, int $itemsPerPage): array;

    /**
     * @param AbstractEntityInterface $entity
     *
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function save($entity): void;

    /**
     * @param AbstractEntityInterface $entity
     *
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function delete($entity): void;
}
