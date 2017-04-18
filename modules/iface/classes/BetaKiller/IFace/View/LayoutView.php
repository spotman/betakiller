<?php
namespace BetaKiller\IFace\View;

use View;

class LayoutView
{
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
        $view_path = $this->getViewPath();

        return $this->viewFactory($view_path)
            ->set('content', $this->_content)
            ->render();
    }

    /**
     * @param string $_content
     *
     * @return $this
     */
    public function setContent($_content)
    {
        // Force content rendering
        $this->_content = (string)$_content;

        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->_content;
    }

    public function getViewPath()
    {
        return 'layouts'.DIRECTORY_SEPARATOR.$this->_path;
    }

    /**
     * @param $path
     *
     * @return View
     */
    protected function viewFactory($path)
    {
        return View::factory($path);
    }

    /**
     * The __toString method allows a class to decide how it will react when it is converted to a string.
     *
     * @return string
     * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
     */
    public function __toString()
    {
        return $this->render();
    }
}
