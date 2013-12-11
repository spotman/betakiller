<?php defined('SYSPATH') OR die('No direct script access.');

interface IFace_Model {

//    use Util_GetterAndSetterMethod;
//
//    protected $_provider;

    // TODO

    /**
     * Returns list of child iface models
     *
     * @return IFace_Model[]
     */
    public function get_children();

    /**
     * Return parent iface model or NULL
     *
     * @return IFace_Model
     */
    public function get_parent();


    /**
     * Returns iface codename
     *
     * @return string
     */
    public function get_codename();

    /**
     * Returns iface url part
     *
     * @return string
     */
    public function get_uri();

    /**
     * Returns TRUE if iface is marked as "default"
     *
     * @return bool
     */
    public function is_default();

    /**
     * Returns array representation of the model data
     *
     * @return array
     */
    public function as_array();

//    /**
//     * Getter for model data
//     *
//     * @param $key
//     * @return mixed
//     */
//    public function get($key);

//    /**
//     * Provider factory (for current model type)
//     *
//     * @return IFace_Provider
//     */
//    protected abstract function get_provider();
//
//    /**
//     * Getter/setter for model provider
//     *
//     * @param IFace_Provider $provider
//     * @return IFace_Provider
//     */
//    public function provider(IFace_Provider $provider = NULL)
//    {
//        return $this->getter_and_setter_method('_provider', $provider, 'get_provider');
//    }


}