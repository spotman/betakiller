<?php defined('SYSPATH') OR die('No direct script access.');


class IFace_Model_Provider_DB extends IFace_Model_Provider {

    /**
     * Returns list of root elements
     *
     * @return Model_IFace[]
     */
    public function get_root()
    {
        return $this->orm_factory()
            ->where('parent_id', 'IS', NULL)
            ->find_all()->as_array();
    }

    /**
     * Returns default iface model in current provider
     *
     * @return Model_IFace|NULL
     */
    public function get_default()
    {
        $iface = $this->orm_factory()
            ->where('is_default', '=', TRUE)
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
        $iface = $this->orm_factory()
            ->where('codename', '=', $codename)
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