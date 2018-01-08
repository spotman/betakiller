<?php
namespace BetaKiller\Repository;


use BetaKiller\Model\IFaceLayout;
use BetaKiller\Model\LayoutInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

/**
 * Class IFaceLayoutRepository
 *
 * @package BetaKiller\Repository
 * @method IFaceLayout getOrmInstance()
 */
class IFaceLayoutRepository extends AbstractOrmBasedRepository
{
    /**
     * @return LayoutInterface
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getDefault(): LayoutInterface
    {
        $orm = $this->getOrmInstance();

        $this->filterIsDefault($orm, true);

        // Cache coz it is used widely
        $orm->cached();

        $default = $this->findOne($orm);

        if (!$default) {
            throw new RepositoryException('No default layout found; set it, please');
        }

        return $default;
    }

    private function filterIsDefault(OrmInterface $orm, ?bool $value = null): self
    {
        $orm->where('is_default', '=', $value ?? true);

        return $this;
    }
}
