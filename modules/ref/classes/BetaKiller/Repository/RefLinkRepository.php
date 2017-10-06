<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\RefLink;
use BetaKiller\Model\RefPage;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

/**
 * Class RefLinkRepository
 *
 * @package BetaKiller\Repository
 * @method RefLink findById(int $id)
 * @method RefLink create()
 * @method RefLink[] getAll()
 */
class RefLinkRepository extends AbstractOrmBasedRepository
{
    /**
     * @param string $name
     *
     * @return \BetaKiller\Model\RefLink|null
     * @throws \Kohana_Exception
     */
    public function getByName(string $name): ?RefLink
    {
        $orm = $this->getOrmInstance();

        $model = $orm->where('name', '=', $name)->find();

        return $model->loaded() ? $model : null;
    }

    public function findBySourceAndTarget(?RefPage $source, RefPage $target, ?bool $createMissing = null): RefLink
    {
        $createMissing = $createMissing ?? true;

        $orm = $this->getOrmInstance();

        $this->filterSource($orm, $source);
        $this->filterTarget($orm, $target);

        $link = $orm->find();
        $link = $link->loaded() ? $link : null;

        if (!$link && $createMissing) {
            $link = $this->create()
                ->setSource($source)
                ->setTarget($target);
        }

        if (!$link) {
            throw new RepositoryException('Can not create link for :source => :target', [
                ':source' => $source ? $source->getID() : 'no-ref',
                ':target' => $target->getID(),
            ]);
        }

        return $link;
    }

    private function filterSource(OrmInterface $orm, ?RefPage $source)
    {
        if ($source) {
            $orm->where('source_id', '=', $source->getID());
        } else {
            $orm->where('source_id', 'IS', null);
        }
    }

    private function filterTarget(OrmInterface $orm, RefPage $target)
    {
        $orm->where('target_id', '=', $target->getID());
    }
}
