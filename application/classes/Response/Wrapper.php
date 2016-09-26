<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Response_Wrapper {

    protected $_type;

    protected $_content;

    /**
     * @param string $type
     * @return static
     */
    public static function factory($type)
    {
        $type = strtolower($type);

        $class_name = 'Response_Wrapper_'. ucfirst($type);
        return new $class_name($type);
    }

    public function __construct($type)
    {
        $this->_type = $type;
    }

    public function set_content($content)
    {
        $this->_content = $content;
        return $this;
    }

    public function get_content()
    {
        return $this->_content;
    }

    abstract public function render();

    protected function view($file = NULL)
    {
        return View_Wrapper::factory($file ?: $this->_type);
    }

}
