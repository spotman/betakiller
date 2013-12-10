<?php defined('SYSPATH') OR die('No direct script access.');

interface IFace_Provider_Interface {

    /**
     * Returns list of root elements
     *
     * @return IFace_Model[]
     */
    public function get_root();

    /**
     * Returns default iface model in current provider
     *
     * @return IFace_Model
     */
    public function get_default();

    /**
     * Returns iface model by codename or NULL if none was found
     *
     * @param $codename
     * @return IFace_Model|null
     */
    public function by_codename($codename);

    /**
     * Performs iface model search by uri (and optional parent iface model)
     *
     * @param string $uri
     * @param IFace_Model|null $parent_model
     * @return IFace_Model
     */
    public function by_uri($uri, IFace_Model $parent_model = NULL);

}