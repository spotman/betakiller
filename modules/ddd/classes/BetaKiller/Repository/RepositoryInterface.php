<?php
namespace BetaKiller\Repository;

interface RepositoryInterface
{
    public const CLASS_PREFIX = 'Repository';
    public const CLASS_SUFFIX = 'Repository';

    public static function getCodename(): string;

    /**
     * @param string $id
     *
     * @return mixed
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findById(string $id);

    /**
     * @param string $id
     *
     * @return mixed
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getById(string $id);

    /**
     * @return \BetaKiller\Model\AbstractEntityInterface[]
     */
    public function getAll(): array;

    /**
     * @param $entity
     *
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function save($entity): void;

    /**
     * @param $entity
     *
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function delete($entity): void;
}
