<?php

use BetaKiller\IFace\Core\IFace;

abstract class Core_View_IFace {

    /**
     * @var string
     */
    protected $_layout;

    /**
     * @var string
     */
    protected $_wrapper = \View_Wrapper::HTML5;

    /**
     * @var array
     */
    protected $_data = array();

    /**
     * Helper for changing wrapper from view
     *
     * @param string $wrapper
     */
    public function wrapper($wrapper)
    {
        $this->_wrapper = $wrapper;
    }

    public function render(IFace $iface)
    {
        $view_path = $this->get_view_path($iface);
        $iface_view = $this->view_factory($view_path);

        // Getting IFace data
        $this->_data = $iface->get_data();

//        // For changing wrapper from view via $_this->wrapper('html')
//        $this->_data['_iface'] = $this;

        $iface_view->set($this->_data);

        $meta = Meta::instance();

        // Setting page title
        $meta->title( $iface->get_title() );

        // Setting page description
        $meta->description( $iface->get_description() );

        Link::instance()
            ->canonical($iface->url(null, false));

        // TODO move calls for Meta and Link to overrided methods in Wrapper

        // Getting IFace layout
        $this->_layout = $iface->get_layout_codename();

        $layout = $this->process_layout($iface_view);

        return $this->process_wrapper($layout);
    }

    protected function process_layout(View $iface_view)
    {
        // TODO DI
        return View_Layout::factory($this->_layout)
            ->set_content($iface_view)
            ->render();
    }

    protected function process_wrapper($layout)
    {
        // TODO DI
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

    protected function get_view_path(IFace $iface)
    {
        return 'ifaces'. DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $iface->get_codename());
    }

}
