<?php
namespace BetaKiller\Repository;

interface RepositoryInterface
{
    public const CLASS_PREFIX = 'Repository';
    public const CLASS_SUFFIX = 'Repository';

    public static function getCodename(): string;

    /**
     * @param int $id
     *
     * @return mixed
     */
    public function findById(int $id);

    /**
     * @return \BetaKiller\Model\AbstractEntityInterface[]|\Traversable
     */
    public function getAll();

    /**
     * Creates empty entity
     *
     * @return mixed
     */
    public function create();

    /**
     * @param $entity
     */
    public function save($entity): void;

    /**
     * @param $entity
     */
    public function delete($entity): void;
}
