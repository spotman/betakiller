<?php

use BetaKiller\IFace\IFaceModelInterface;

class IFace_Model_Provider_Admin_Model implements IFaceModelInterface
{
    use \BetaKiller\Utils\Kohana\TreeModelTrait;

    /**
     * @var IFace_Model_Provider_Admin
     */
    protected $_provider;

    protected $_codename;

    protected $_parent_codename;

    protected $_uri;

    protected $_label;

    protected $_title;

    /**
     * @var bool
     */
    protected $_has_dynamic_url;

    /**
     * @var bool
     */
    protected $_has_tree_behaviour;

    /**
     * @var bool
     */
    protected $_hide_in_site_map;

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
     * @return int
     * @throws HTTP_Exception_501
     */
    public function get_id()
    {
        throw new HTTP_Exception_501('Admin IFace model have no ID');
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
     * @return IFaceModelInterface|null
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
     * @return \BetaKiller\IFace\IFaceModelInterface[]
     */
    public function get_root()
    {
        return $this->get_provider()->get_root();
    }

    /**
     * Returns list of child iface models
     *
     * @return \BetaKiller\IFace\IFaceModelInterface[]
     */
    public function get_children()
    {
        return $this->get_provider()->get_childs($this);
    }

    /**
     * @param string|null $column
     * @return int[]
     * @throws HTTP_Exception_501
     */
    public function get_all_children($column = NULL)
    {
        throw new HTTP_Exception_501('Not implemented yet');
    }

    /**
     * @param \BetaKiller\Utils\Kohana\TreeModelInterface|null $parent
     * @return $this
     * @throws HTTP_Exception_501
     */
    public function set_parent(\BetaKiller\Utils\Kohana\TreeModelInterface $parent = NULL)
    {
        throw new HTTP_Exception_501('Admin model can not change parent');
    }

    /**
     * Sets title for using in <title> tag
     *
     * @param string $value
     * @return $this
     * @throws HTTP_Exception_501
     */
    public function set_title($value)
    {
        throw new HTTP_Exception_501('Admin model can not change title');
    }

    /**
     * Sets description for using in <meta> tag
     *
     * @param string $value
     * @return $this
     * @throws HTTP_Exception_501
     */
    public function set_description($value)
    {
        throw new HTTP_Exception_501('Admin model can not change description');
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
     * Returns label for using in breadcrumbs and etc
     *
     * @return string
     */
    public function get_label()
    {
        return $this->_label;
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
     * Returns description for using in <meta> tag
     *
     * @return string
     */
    public function get_description()
    {
        // Admin IFace does not need description
        return NULL;
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
            'parentCodename'    => $this->get_parent_codename(),
            'label'             => $this->get_label(),
            'title'             => $this->get_title(),
            'hasDynamicUrl'     => $this->has_dynamic_url(),
            'hideInSiteMap'     => $this->hide_in_site_map(),
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

        $this->_label = isset($data['label']) ? $data['label'] : NULL;
        $this->_title = isset($data['title']) ? $data['title'] : NULL;

        if ( isset($data['parentCodename']) )
        {
            $this->_parent_codename = $data['parentCodename'];
        }

        if ( isset($data['hasDynamicUrl']) )
        {
            $this->_has_dynamic_url = TRUE;
        }

        if ( isset($data['hasTreeBehaviour']) )
        {
            $this->_has_tree_behaviour = TRUE;
        }

        if ( isset($data['hideInSiteMap']) )
        {
            $this->_hide_in_site_map = TRUE;
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
     * Returns TRUE if iface provides tree-like url mapping
     *
     * @return bool
     */
    public function has_tree_behaviour()
    {
        return (bool) $this->_has_tree_behaviour;
    }

    /**
     * @return bool
     */
    public function hide_in_site_map()
    {
        return (bool) $this->_hide_in_site_map;
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
