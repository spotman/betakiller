<?php defined('SYSPATH') OR die('No direct script access.');

class IFace_Model_Provider_Admin_Model implements IFace_Model {

    /**
     * @var IFace_Model_Provider_Admin
     */
    protected $_provider;

    protected $_codename;

    protected $_parent_codename;

    protected $_uri;

    protected $_title;

    /**
     * @var bool
     */
    protected $_has_dynamic_url;

    public static function factory($data, IFace_Model_Provider_Admin $provider)
    {
        /** @var self $instance */
        $instance = new static;
        $instance->from_array($data);
        $instance->set_provider($provider);
        return $instance;
    }

    /**
     * Returns TRUE if iface is marked as "default"
     *
     * @return bool
     */
    public function is_default()
    {
        // Admin IFaces can not have "is_default" marker
        return FALSE;
    }

    /**
     * Returns iface url part
     *
     * @return string
     */
    public function get_uri()
    {
        return $this->_uri;
    }

    /**
     * Return parent iface model or NULL
     *
     * @return IFace_Model
     */
    public function get_parent()
    {
        if ( ! $this->_parent_codename )
            return NULL;

        return $this->get_provider()->by_codename($this->_parent_codename);
    }

    /**
     * Returns codename of parent IFace or NULL
     *
     * @return string
     */
    public function get_parent_codename()
    {
        return $this->_parent_codename;
    }

    /**
     * Returns list of child iface models
     *
     * @return IFace_Model[]
     */
    public function get_children()
    {
        return $this->get_provider()->get_childs($this);
    }

    /**
     * Returns iface codename
     *
     * @return string
     */
    public function get_codename()
    {
        return $this->_codename;
    }

    /**
     * Returns title for using in page <title> tag
     *
     * @return string
     */
    public function get_title()
    {
        return $this->_title;
    }

    /**
     * Returns array representation of the model data
     *
     * @return array
     */
    public function as_array()
    {
        return array(
            'codename'          => $this->get_codename(),
            'uri'               => $this->get_uri(),
            'parent'            => $this->get_parent_codename(),
            'title'             => $this->get_title(),
            'has_dynamic_url'   => $this->has_dynamic_url(),
        );
    }

    /**
     * Returns layout codename
     *
     * @return string
     */
    public function get_layout_codename()
    {
        // Admin IFaces always have "admin" layout
        return 'admin';
    }

    public function from_array(array $data)
    {
        $this->_codename = $data['codename'];
        $this->_uri = $data['uri'];

        $this->_title = $data['title'];

        if ( isset($data['parent_codename']) )
        {
            $this->_parent_codename = $data['parent_codename'];
        }

        if ( isset($data['has_dynamic_url']) )
        {
            $this->_has_dynamic_url = TRUE;
        }
    }

    /**
     * Returns TRUE if iface provides dynamic url mapping
     *
     * @return bool
     */
    public function has_dynamic_url()
    {
        return (bool) $this->_has_dynamic_url;
    }

    /**
     * @return IFace_Model_Provider_Admin
     */
    protected function get_provider()
    {
        return $this->_provider;
    }

    public function set_provider(IFace_Model_Provider_Admin $provider)
    {
        $this->_provider = $provider;
        return $this;
    }

}
