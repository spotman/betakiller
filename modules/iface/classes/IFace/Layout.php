<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class IFace_Layout
 *
 * @TODO Meta module + fetching CSS/JS data
 */
class IFace_Layout {

    /**
     * @var string Codename of layout (filename)
     */
    protected $_codename;

    /**
     * @var string Inner content for wrapping with layout
     */
    protected $_content;

    public static function by_codename($codename)
    {
        return new static($codename);
    }

    /**
     * @param string $codename
     */
    public function __construct($codename)
    {
        $this->_codename = $codename;
    }

    public function set_content($content)
    {
        $this->_content = $content;
        return $this;
    }

    /**
     * @return string
     */
    public function render()
    {
        return View_Layout::factory($this->_codename)
            ->set_content($this->_content)
            ->render();
    }

}