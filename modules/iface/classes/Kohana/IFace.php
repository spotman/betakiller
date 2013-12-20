<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Kohana_IFace {

    use Util_GetterAndSetterMethod, Util_Factory_Cached
    {
        Util_Factory_Cached::factory as protected _factory;
    }

    /**
     * @var string
     */
    protected $_codename;

    /**
     * @var IFace_Model
     */
    protected $_model;

    /**
     * @var IFace Parent iface
     */
    protected $_parent;

    /**
     * @var array
     */
    protected static $_instance_cache = array();

    /**
     * @param string $codename IFace codename
//     * @param IFace_Model $model
     * @return static
     * @throws IFace_Exception
     */
    public static function by_codename($codename)
    {
        if ( ! $codename )
            throw new IFace_Exception('Can not create IFace from empty codename');

        $model = IFace_Provider::instance()->by_codename($codename);

        return static::factory($model);
    }

    /**
     * Creates instance of IFace from model
     *
     * @param IFace_Model $model
     * @return static
     */
    public static function factory(IFace_Model $model)
    {
        $codename = $model->get_codename();

        return static::_factory($codename, $model);
    }

    protected static function instance_factory($codename, IFace_Model $model)
    {
        $class_name = static::get_class_prefix().$codename;

        if ( ! class_exists($class_name) )
        {
            $class_name = static::get_class_prefix().'Default';
        }

        /** @var IFace $object */
        $object = new $class_name;

        if ( ! ($object instanceof IFace) )
            throw new IFace_Exception('Class :class must be instance of class IFace', array(':class' => $class_name));

        $object->codename($codename);
        $object->model($model);

        return $object;
    }

    public function __construct()
    {
        // Empty by default
    }

    /**
     * @param string|null $codename
     * @return $this|string
     */
    public function codename($codename = NULL)
    {
        return $this->getter_and_setter_method('_codename', $codename);
    }

    /**
     * @return string
     */
    public function render()
    {
        // Setting page title
        Meta::instance()->title( $this->get_title() );

        // Getting IFace View instance and rendering
        return $this->get_view()->render();
    }

    public function get_layout_codename()
    {
        return $this->model()->get_layout_codename();
    }

    public function get_title()
    {
        return $this->model()->get_title();
    }

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function get_data()
    {
        // Empty by default
        return array();
    }

    public function __toString()
    {
        return (string) $this->render();
    }

    /**
     * Getter/setter for current iface parent
     * @param IFace|null $parent
     * @return IFace|null
     */
    public function parent(IFace $parent = NULL)
    {
        return $this->getter_and_setter_method('_parent', $parent, 'get_parent');
    }

    protected function get_parent()
    {
        $parent_model = $this->model()->get_parent();

        if ( ! $parent_model )
            return NULL;

        return static::factory($parent_model);
    }

    /**
     * Getter/setter for current iface model
     * @param IFace_Model $model
     * @return IFace_Model
     */
    public function model(IFace_Model $model = NULL)
    {
        return $this->getter_and_setter_method('_model', $model, 'model_factory');
    }

    protected function model_factory()
    {
        return IFace_Provider::instance()->by_codename($this->_codename);
    }

    public function is_default()
    {
        return $this->model()->is_default();
    }

    // TODO
    public function url()
    {
        $url = '/'.$this->get_url();

        $parent = $this->parent();

        if ( $parent )
        {
            $url = $parent->url().$url;
        }

        return $url;
    }

    protected function get_url()
    {
        $url = $this->model()->get_uri();

        // TODO replace dynamic locations with their actual values

        return $url;
    }

    protected function get_view()
    {
        return View_IFace::factory($this);
    }

    protected static function get_class_prefix()
    {
        return 'IFace_';
    }
}