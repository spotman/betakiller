<?php use BetaKiller\IFace\IFaceModelInterface;

defined('SYSPATH') OR die('No direct script access.');

interface IFace_Model_Provider_Interface {

    /**
     * Returns list of root elements
     *
     * @return IFaceModelInterface[]
     */
    public function get_root();

    /**
     * Returns default iface model in current provider
     *
     * @return IFaceModelInterface
     */
    public function get_default();

    /**
     * Returns iface model by codename or NULL if none was found
     *
     * @param $codename
     * @return IFaceModelInterface|null
     */
    public function by_codename($codename);

}
