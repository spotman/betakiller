<?php
namespace BetaKiller\IFace\View;

use BetaKiller\View\ViewInterface;
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

    /**
     * @var string
     */
    protected $_codename;

    /**
     * @var string
     */
    protected $_content;

    public static function factory(?string $codename = null)
    {
        return new static($codename ?? self::HTML5);
    }

    public function __construct(string $_codename)
    {
        $this->_codename = $_codename;
    }

    /**
     * @param string $_content
     *
     * @return $this
     */
    public function setContent(string $_content): WrapperView
    {
        $this->_content = $_content;

        return $this;
    }

    public function render(): string
    {
        $path = $this->getViewPath($this->_codename);

        $view = $this->viewFactory($path);

        foreach ($this->getData() as $key => $value) {
            $view->set($key, $value);
        }

        return $view->set('content', $this->_content)->render();
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
     * @return \BetaKiller\View\ViewInterface
     */
    protected function viewFactory(string $view_path): ViewInterface
    {
        return View::factory($view_path);
    }

    protected function getViewPath(string $codename): string
    {
        return 'wrappers'.DIRECTORY_SEPARATOR.$codename;
    }
}
