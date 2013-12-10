<?php defined('SYSPATH') OR die('No direct script access.');


class IFace_Provider_Source_DB extends IFace_Provider_Source {

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
     * @return Model_IFace
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
     * @return Model_IFace
     */
    public function by_codename($codename)
    {
        $iface = $this->orm_factory()
            ->where('codename', '=', $codename)
            ->find();

        return $iface->loaded() ? $iface : NULL;
    }

    protected function orm_factory()
    {
        return ORM::factory('IFace');
    }


//    /**
//     * Returns parent iface model
//     *
//     * @param IFace_Model $model
//     * @return IFace_Model|null
//     */
//    public function get_parent(IFace_Model $model)
//    {
//        /** @var Model_IFace $parent */
//        $parent = $model->get('parent');
//
//        return $parent->loaded() ? $parent : NULL;
//    }
//
//
//    /**
//     * Returns parent iface model
//     *
//     * @param IFace_Model $model
//     * @return IFace_Model[]
//     */
//    public function get_children(Model_IFace $model)
//    {
//        $model->find_all();
//        // TODO: Implement get_children() method.
//
//    }
}