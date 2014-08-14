<?php defined('SYSPATH') OR die('No direct script access.');


class IFace_Model_Provider_DB extends IFace_Model_Provider_Abstract {

    /**
     * Returns list of root elements
     *
     * @return Model_IFace[]
     */
    public function get_root()
    {
        $orm = $this->orm_factory();

        return $orm
            ->where($orm->object_name().'.parent_id', 'IS', NULL)
            ->cached()
            ->find_all()
            ->as_array();
    }

    /**
     * Returns default iface model in current provider
     *
     * @return Model_IFace|NULL
     */
    public function get_default()
    {
        $orm = $this->orm_factory();

        $iface = $orm
            ->where($orm->object_name().'.is_default', '=', TRUE)
            ->cached()
            ->find();

        return $iface->loaded() ? $iface : NULL;
    }

    /**
     * Returns iface model by codename or NULL if none was found
     *
     * @param $codename
     * @return Model_IFace|NULL
     */
    public function by_codename($codename)
    {
        $orm = $this->orm_factory();

        $iface = $orm
            ->where($orm->object_name().'.codename', '=', $codename)
            ->cached()
            ->find();

        return $iface->loaded() ? $iface : NULL;
    }

    /**
     * @return Model_IFace
     */
    protected function orm_factory()
    {
        return ORM::factory('IFace');
    }

}
