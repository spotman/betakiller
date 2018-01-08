<?php
namespace BetaKiller\View;

interface LayoutViewInterface
{
    public const WRAPPER_HTML5 = 'html5';

    /**
     * @return string
     */
    public function getLayoutCodename(): string;

    /**
     * @param string $layoutCodename
     *
     * @return \BetaKiller\View\LayoutViewInterface
     */
    public function setLayoutCodename(string $layoutCodename): LayoutViewInterface;

    /**
     * @return string
     */
    public function getWrapperCodename(): string;

    /**
     * @param string $wrapperCodename
     *
     * @return \BetaKiller\View\LayoutViewInterface
     */
    public function setWrapperCodename(string $wrapperCodename): LayoutViewInterface;

    /**
     * @param \BetaKiller\View\ViewInterface $ifaceView
     *
     * @return string
     */
    public function render(ViewInterface $ifaceView): string;
}
