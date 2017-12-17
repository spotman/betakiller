<?php
namespace BetaKiller\IFace\View;

use View;

/**
 * View wrapper
 *
 * @package BetaKiller
 * @author Spotman
 */
class WrapperView
{
    public const HTML5 = 'html5';

    protected $_codename;

    /**
     * @var string
     */
    protected $_content;

    public static function factory(?string $codename = null)
    {
        return new static($codename ?? self::HTML5);
    }

    public function __construct($_codename)
    {
        $this->_codename = $_codename;
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

    public function render()
    {
        $path = $this->getViewPath($this->_codename);

        return $this
            ->viewFactory($path)
            ->set($this->getData())
            ->set('content', $this->_content)
            ->render();
    }

    /**
     * Hook for providing custom data to wrapper
     *
     * @return array
     */
    protected function getData(): array
    {
        return [];
    }

    /**
     * @param $view_path
     *
     * @return View
     */
    protected function viewFactory(string $view_path)
    {
        return View::factory($view_path);
    }

    protected function getViewPath(string $codename): string
    {
        return 'wrappers'.DIRECTORY_SEPARATOR.$codename;
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
