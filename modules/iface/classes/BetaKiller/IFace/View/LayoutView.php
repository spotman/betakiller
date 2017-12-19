<?php
namespace BetaKiller\IFace\View;

use BetaKiller\View\ViewInterface;
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

    public static function factory(string $_path)
    {
        return new static($_path);
    }

    protected function __construct($_path)
    {
        $this->_path = $_path;
    }

    /**
     * @return string
     * @throws \View_Exception
     */
    public function render(): string
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
    public function setContent(string $_content): LayoutView
    {
        // Force content rendering
        $this->_content = $_content;

        return $this;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->_content;
    }

    public function getViewPath(): string
    {
        return 'layouts'.DIRECTORY_SEPARATOR.$this->_path;
    }

    /**
     * @param $path
     *
     * @return \BetaKiller\View\ViewInterface
     */
    protected function viewFactory(string $path): ViewInterface
    {
        return View::factory($path);
    }
}
