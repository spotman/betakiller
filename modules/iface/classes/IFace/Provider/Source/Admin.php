<?php defined('SYSPATH') OR die('No direct script access.');


class IFace_Provider_Source_Admin extends IFace_Provider_Source {

    /**
     * Returns list of root elements
     *
     * @return IFace_Model[]
     */
    public function get_root()
    {
        // TODO: Implement get_root() method.
        return array();
    }

    /**
     * Returns default iface model in current provider
     *
     * @return IFace_Model
     */
    public function get_default()
    {
        // TODO: Implement get_default() method.
    }

    /**
     * Returns iface model by codename or NULL if none was found
     *
     * @param $codename
     * @return IFace_Model
     */
    public function by_codename($codename)
    {
        // TODO: Implement by_codename() method.
    }

}