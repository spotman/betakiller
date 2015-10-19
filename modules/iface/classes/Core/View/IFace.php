<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Core_View_IFace {

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

    public static function factory(Core_IFace $iface)
    {
        return new static($iface);
    }

    protected function __construct(Core_IFace $iface)
    {
        $this->_iface = $iface;
    }

    /**
     * Helper for changing wrapper from view
     *
     * @param string $wrapper
     */
    public function wrapper($wrapper)
    {
        $this->_wrapper = $wrapper;
    }

    public function render()
    {
        $view_path = $this->get_view_path();

        $iface_view = $this->view_factory($view_path);

        // Getting IFace data
        $this->_data = $this->_iface->get_data();

//        // For changing wrapper from view via $_this->wrapper('html')
//        $this->_data['_iface'] = $this;

        $iface_view->set($this->_data);

        // Setting page title
        Meta::instance()->title( $this->_iface->get_title() );

        // Setting page description
        Meta::instance()->description( $this->_iface->get_description() );

        // Getting IFace layout
        $this->_layout = $this->_iface->get_layout_codename();

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
        return 'ifaces'. DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $this->_iface->get_codename());
    }

}
