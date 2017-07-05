<?php
namespace BetaKiller\Repository;

interface RepositoryInterface
{
    const CLASS_PREFIX = 'Repository';
    const CLASS_SUFFIX = 'Repository';

    public static function getCodename(): string;

    /**
     * @param int $id
     *
     * @return mixed
     */
    public function findById(int $id);

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
