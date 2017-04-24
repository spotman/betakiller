<?php
namespace BetaKiller\IFace\ModelProvider;

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
        $orm = $this->ormFactory();

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
        $orm = $this->ormFactory();

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
        $orm = $this->ormFactory();

        $iface = $orm
            ->where($orm->object_column('codename'), '=', $codename)
            ->cached()
            ->find();

        return $iface->loaded() ? $iface : null;
    }

    /**
     * @return IFace
     */
    protected function ormFactory()
    {
        return ORM::factory('IFace');
    }
}
