<?php
namespace BetaKiller\IFace\ModelProvider;

use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Model\IFace;
use ORM;

class IFaceModelProviderDatabase extends IFaceModelProviderAbstract
{
    /**
     * Returns list of root elements
     *
     * @return IFace[]
     */
    public function getRoot()
    {
        $orm = $this->createIFaceOrm();

        return $orm
            ->where($orm->object_column('parent_id'), 'IS', null)
            ->cached()
            ->find_all()
            ->as_array();
    }

    /**
     * Returns default iface model in current provider
     *
     * @return IFace|NULL
     */
    public function getDefault()
    {
        $orm = $this->createIFaceOrm();

        $iface = $orm
            ->where($orm->object_column('is_default'), '=', true)
            ->cached()
            ->find();

        return $iface->loaded() ? $iface : null;
    }

    /**
     * Returns iface model by codename or NULL if none was found
     *
     * @param $codename
     *
     * @return IFace|NULL
     */
    public function getByCodename($codename)
    {
        $orm = $this->createIFaceOrm();

        $iface = $orm
            ->where($orm->object_column('codename'), '=', $codename)
            ->cached()
            ->find();

        return $iface->loaded() ? $iface : null;
    }

    /**
     * Search for IFace linked to provided entity, entity action and zone
     *
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param string                                        $entityAction
     * @param string                                        $zone
     *
     * @return IFaceModelInterface|null
     */
    public function getByEntityActionAndZone(DispatchableEntityInterface $entity, $entityAction, $zone)
    {
        $orm = $this->createIFaceOrm();

        $iface = $orm
            ->where('entity.model_name', '=', $entity->getModelName())
            ->where('action.name', '=', $entityAction)
            ->where('zone.name', '=', $zone)
            ->find();

        return $iface->loaded() ? $iface : null;
    }

    /**
     * @return \BetaKiller\Model\IFace
     */
    protected function createIFaceOrm()
    {
        return ORM::factory('IFace');
    }
}
