<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Core_IFace {

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
        return static::provider()->by_codename($codename);
    }

    /**
     * Creates instance of IFace from model
     *
     * @param IFace_Model $model
     * @return static
     */
    public static function factory(IFace_Model $model)
    {
        return static::provider()->from_model($model);
    }

    protected static function provider()
    {
        return IFace_Provider::instance();
    }

    public function __construct()
    {
        // Empty by default
    }

    /**
     * @return $this|string
     */
    public function get_codename()
    {
        return $this->get_model()->get_codename();
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
        return $this->get_model()->get_layout_codename();
    }

    public function get_title()
    {
        return $this->get_model()->get_title();
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

    public function get_parent()
    {
        if ( ! $this->_parent )
        {
            $this->_parent = $this->provider()->get_parent($this);
        }

        return $this->_parent;
    }

    function set_parent(IFace $parent)
    {
        $this->_parent = $parent;
        return $this;
    }

    /**
     * Getter for current iface model
     *
     * @return IFace_Model
     */
    public function get_model()
    {
        return $this->_model;
    }

    /**
     * Setter for current iface model
     *
     * @param IFace_Model $model
     * @return $this
     */
    public function set_model(IFace_Model $model)
    {
        $this->_model = $model;
        return $this;
    }

    public function is_default()
    {
        return $this->get_model()->is_default();
    }

    public function is_in_stack()
    {
        return URL_Dispatcher::instance()->in_stack($this);
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
                $parent = $current->get_parent();
                $current = $parent;
            }
            while ( $parent );
        }

        return URL::site('/'.implode('/', array_reverse($parts)), TRUE);
    }

    protected function make_uri(URL_Parameters $parameters = NULL)
    {
        return $this->get_model()->has_dynamic_url()
            ? URL_Dispatcher::instance()->make_dynamic_uri_part($this->get_uri(), $parameters)
            : $this->get_uri();
    }

    protected function get_uri()
    {
        return $this->get_model()->get_uri();
    }

    public function get_view()
    {
        return View_IFace::factory($this);
    }

}
