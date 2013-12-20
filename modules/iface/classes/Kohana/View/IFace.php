<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Kohana_View_IFace {

    /**
     * @var IFace
     */
    protected $_iface;

    /**
     * @var string
     */
    protected $_layout;

    /**
     * @var string
     */
    protected $_wrapper = View_Wrapper::HTML5;

    /**
     * @var array
     */
    protected $_data = array();

    public static function factory(IFace $iface)
    {
        return new static($iface);
    }

    protected function __construct(IFace $iface)
    {
        $this->_iface = $iface;
        $this->_iface_codename = $iface->codename();
        $this->_layout = $iface->get_layout_codename();
        $this->_data = $iface->get_data();
    }

    /**
     * Helper for changing wrapper from view
     *
     * @param string $wrapper
     * @return $this
     */
    public function wrapper($wrapper)
    {
        $this->_wrapper = $wrapper;
        return $this;
    }

    public function render()
    {
        $view_path = $this->get_view_path();

        $iface_view = $this->view_factory($view_path);

        // For changing wrapper from view via $_this->wrapper('html')
        $this->_data['_this'] = $this;

        $iface_view->set($this->_data);

        $layout = $this->process_layout($iface_view);

        return $this->process_wrapper($layout);
    }

    protected function process_layout(View $iface_view)
    {
        return View_Layout::factory($this->_layout)
            ->set_content($iface_view)
            ->render();
    }

    protected function process_wrapper($layout)
    {
        return View_Wrapper::factory($this->_wrapper)
            ->set_content($layout)
            ->render();
    }

    /**
     * @param $path
     * @return View
     */
    protected function view_factory($path)
    {
        return View::factory($path);
    }

    protected function get_view_path()
    {
        return 'ifaces'. DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $this->_iface->codename());
    }

}