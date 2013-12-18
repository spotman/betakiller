<?php defined('SYSPATH') OR die('No direct script access.');

interface IFace_Model {

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

    /**
     * Returns iface layout object
     *
     * @return IFace_Layout
     */
    public function get_layout_codename();

}