<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Core_IFace {

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
     * @var DateTime
     */
    protected $_last_modified;

    /**
     * Creates IFace instance from it`s codename
     *
     * @param string $codename IFace codename
     * @return static
     * @throws IFace_Exception
     */
    public static function by_codename($codename)
    {
        if ( ! $codename )
            throw new IFace_Exception('Can not create IFace from empty codename');

        $model = IFace_Model_Provider::instance()->by_codename($codename);

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

//        // Check for dynamic url implementation
//        if ( $model->has_dynamic_url() AND ! ($object instanceof IFace_Dispatchable) )
//            throw new IFace_Exception('IFace :class_name has dynamic url but does not implementing IFace_Dispatchable',
//                array(':class_name' => $class_name)
//            );

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

    /**
     * @param \DateTime|NULL $last_modified
     */
    public function set_last_modified(DateTime $last_modified)
    {
        $this->_last_modified = $last_modified;
    }

    /**
     * @return \DateTime
     */
    public function get_last_modified()
    {
        return $this->_last_modified;
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
        return $this->getter_and_setter_method('_model', $model); // , 'model_factory'
    }

//    protected function model_factory()
//    {
//        return IFace_Model_Provider::instance()->by_codename($this->_codename);
//    }

    public function is_default()
    {
        return $this->model()->is_default();
    }

    public function url(URL_Parameters $parameters = NULL)
    {
        $parts = array();

        if ( ! $this->is_default() )
        {
            $current = $this;

            /** @var IFace $parent */
            $parent = NULL;

            do
            {
                $parts[] = $current->make_uri($parameters);
                $parent = $current->parent();
                $current = $parent;
            }
            while ( $parent );
        }

        return URL::site('/'.implode('/', array_reverse($parts)), TRUE);
    }

    protected function make_uri(URL_Parameters $parameters = NULL)
    {
        return $this->model()->has_dynamic_url()
            ? URL_Dispatcher::instance()->make_uri($this->get_uri(), $parameters)
            : $this->get_uri();
    }

    protected function get_uri()
    {
        return $this->model()->get_uri();
    }

    public function get_view()
    {
        return View_IFace::factory($this);
    }

    protected static function get_class_prefix()
    {
        return 'IFace_';
    }

//    protected function check_parent_instanceof($parent_iface_class_name)
//    {
//        if ( ! ($this->parent() instanceof $parent_iface_class_name) )
//            throw new IFace_Exception('IFace :codename must be child of :parent',
//                array(':codename' => $this->codename(), ':parent' => $parent_iface_class_name)
//            );
//    }
}