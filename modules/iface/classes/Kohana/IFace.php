<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Kohana_IFace {

    use Util_GetterAndSetterMethod;

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
     * @throws IFace_Exception
     * @return static
     */
    public static function factory($codename, IFace_Model $model = NULL) // $codename = NULL
    {
//        if ( ! $codename )
//        {
//            $codename = str_replace(static::get_class_prefix(), '', get_called_class());
//        }

        if ( ! $codename )
            throw new IFace_Exception('Can not create IFace from empty codename');

        // Caching iface instances
        if ( ! isset(static::$_instance_cache[$codename]) )
        {
            static::$_instance_cache[$codename] = static::instance_factory($codename, $model);
        }

        return static::$_instance_cache[$codename];
    }

    protected static function instance_factory($codename, IFace_Model $model = NULL)
    {
        if ( ! $model )
        {
            $model = IFace_Provider::instance()->by_codename($codename);
        }

//        var_dump($codename);
//        var_dump($model->as_array());
//        die();

        $class_name = static::get_class_prefix().$codename;

        if ( ! class_exists($class_name) )
        {
            $class_name = 'IFace_Default';
        }

        /** @var IFace $object */
        $object = new $class_name;

        $object->codename($codename);
        $object->model($model);

        return $object;
    }

    /**
     * Creates instance of IFace from model
     *
     * @param IFace_Model $model
     * @return static
     */
    public static function from_model(IFace_Model $model)
    {
        $codename = $model->get_codename();

        return static::factory($codename, $model);
    }

    /**
     * @param string|null $codename
     * @return $this|string
     */
    public function codename($codename = NULL)
    {
        return $this->getter_and_setter_method('_codename', $codename);
    }

//    public function action_index()
//    {
//        return $this->render();
//    }

    public function render()
    {
        $view = $this->get_view();
        return $view;
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

        return static::from_model($parent_model);
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
        $url = $this->model()->get_url();

        // TODO replace dynamic locations with their actual values

        return $url;
    }

    protected function get_view()
    {
        $view_path = 'iface'. DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $this->_codename);

        return $this->view_factory($view_path);
    }

    private function view_factory($path)
    {
        return View::factory($path);
    }

    private static function get_class_prefix()
    {
        return 'IFace_';
    }
}