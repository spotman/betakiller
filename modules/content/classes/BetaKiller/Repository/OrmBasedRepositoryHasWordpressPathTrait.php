<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\EntityHasWordpressPathInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

/**
 * Trait OrmBasedRepositoryHasWordpressPathTrait
 *
 * @package BetaKiller\Content
 */
trait OrmBasedRepositoryHasWordpressPathTrait
{
    /**
     * @param string $wp_path
     *
     * @return EntityHasWordpressPathInterface|null
     */
    public function findByWpPath($wp_path): ?EntityHasWordpressPathInterface
    {
        /** @var OrmInterface $orm */
        $orm = $this->getOrmInstance();

        $this->filterWpPath($orm, $wp_path);

        /** @var EntityHasWordpressPathInterface $model */
        $model = $orm->find();

        return $model->getID() ? $model : null;
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param string                                    $wp_path
     */
    private function filterWpPath(OrmInterface $orm, string $wp_path)
    {
        $orm->where('wp_path', '=', $wp_path);
    }
}
