<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Kohana_View_Layout {

    /**
     * @var string
     */
    protected $_content;

    /**
     * @var string
     */
    protected $_path;

    public static function factory($_path)
    {
        return new static($_path);
    }

    protected function __construct($_path)
    {
        $this->_path = $_path;
    }

    /**
     * @return string
     */
    public function render()
    {
        $view_path = $this->get_view_path();

        return $this->view_factory($view_path)
            ->set('content', $this->_content)
            ->render();
    }

    /**
     * @param string|View $_content
     * @return $this
     */
    public function set_content($_content)
    {
        // Force content rendering
        $this->_content = (string) $_content;
        return $this;
    }

    public function get_view_path()
    {
        return 'layouts'. DIRECTORY_SEPARATOR . $this->_path;
    }

    /**
     * @param $path
     * @return View
     */
    protected function view_factory($path)
    {
        return View::factory($path);
    }

}
