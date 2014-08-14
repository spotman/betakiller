<?php defined('SYSPATH') OR die('No direct script access.');


class IFace_Model_Provider_Admin extends IFace_Model_Provider_Abstract {

    /**
     * @var IFace_Model_Provider_Admin_Model[]
     */
    protected $_models;

    function __construct()
    {
        $config_files = Kohana::find_file('config', 'ifaces', 'xml');

        if ( ! $config_files )
        {
            throw new IFace_Exception('Missing admin config file');
        }

        foreach ( $config_files as $file )
        {
            $this->load_xml_config($file);
        }
    }

    protected function load_xml_config($file)
    {
        $sxo = simplexml_load_file($file);
        $this->parse_xml_branch($sxo);
    }

    protected function parse_xml_branch(SimpleXMLElement $branch, IFace_Model $parent_model = NULL)
    {
        // Parse branch childs
        foreach ( $branch->children() as $child_node )
        {
            // Parse itself
            $child_node_model = $this->parse_xml_item($child_node, $parent_model);

            // Store model
            $this->set_model($child_node_model);

            // Iterate through childs
            $this->parse_xml_branch($child_node, $child_node_model);
        }
    }

    protected function parse_xml_item(SimpleXMLElement $branch, IFace_Model $parent_model = NULL)
    {
        $attr = $branch->attributes();

        $config = array(
            'codename'          => (string) $attr['codename'],
            'title'             => (string) $attr['title'],
            'uri'               => (string) $attr['uri'],
        );

        if ( $parent_model )
        {
            $config['parent_codename'] = $parent_model->get_codename();
        }

        return $this->model_factory($config);
    }

    protected function model_factory(array $config)
    {
        return IFace_Model_Provider_Admin_Model::factory($config, $this);
    }

    protected function set_model(IFace_Model $model)
    {
        $codename = $model->get_codename();

        if ( isset($this->_models[$codename]) )
            throw new IFace_Exception('Duplicate of codename :codename', array(':codename' => $codename));

        $this->_models[$codename] = $model;
    }

    protected function get_model($codename)
    {
        if ( ! isset($this->_models[$codename]) )
            throw new IFace_Exception('Unknown codename :codename', array(':codename' => $codename));

        return $this->_models[$codename];
    }

    /**
     * Returns list of root elements
     *
     * @return IFace_Model[]
     */
    public function get_root()
    {
        return $this->get_childs();
    }

    /**
     * Returns default iface model in current provider
     *
     * @return IFace_Model
     */
    public function get_default()
    {
        // Admin IFaces can not be marked as "default"
        return NULL;
    }

    /**
     * Returns iface model by codename or NULL if none was found
     *
     * @param $codename
     * @return IFace_Model
     */
    public function by_codename($codename)
    {
        try
        {
            return $this->get_model($codename);
        }
        catch ( IFace_Exception $e )
        {
            return NULL;
        }
    }

    /**
     * Returns list of child nodes of $parent_model (or root nodes if none provided)
     *
     * @param IFace_Model_Provider_Admin_Model $parent_model
     * @return array
     */
    public function get_childs(IFace_Model_Provider_Admin_Model $parent_model = NULL)
    {
        $parent_codename = $parent_model ? $parent_model->get_codename() : NULL;

        $models = array();

        foreach ( $this->_models as $model)
        {
            if ( $model->get_parent_codename() != $parent_codename )
                continue;

            $models[] = $model;
        }

        return $models;
    }

}
