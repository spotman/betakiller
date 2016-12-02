<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class Core_View_Wrapper
 *
 * View wrapper
 *
 * @package BetaKiller
 * @author Spotman
 */
abstract class Core_View_Wrapper {

    const HTML5 = 'html5';

    protected $_codename;

    /**
     * @var string
     */
    protected $_content;

    public static function factory($codename = self::HTML5)
    {
        return new static($codename);
    }

    public function __construct($_codename)
    {
        $this->_codename = $_codename;
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

    public function render()
    {
        $path = $this->get_view_path($this->_codename);

        return $this
            ->view_factory($path)
            ->set($this->get_data())
            ->set('content', $this->_content)
            ->render();
    }

    /**
     * Hook for providing custom data to wrapper
     *
     * @return array
     */
    protected function get_data()
    {
        return array();
    }

    /**
     * @param $view_path
     * @return View
     */
    protected function view_factory($view_path)
    {
        return View::factory($view_path);
    }

    protected function get_view_path($codename)
    {
        return 'wrappers/'.$codename;
    }

//
//    public function get_content()
//    {
//        return $this->get('content');
//    }
//
//    public function set_title($string = '')
//    {
//        $this->set('title', $string);
//        return $this;
//    }
//
//    public function get_title()
//    {
//        return $this->get('title');
//    }

    /**
     * The __toString method allows a class to decide how it will react when it is converted to a string.
     *
     * @return string
     * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
     */
    function __toString()
    {
        return $this->render();
    }
}
