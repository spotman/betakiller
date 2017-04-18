<?php
namespace BetaKiller\View;

use BetaKiller\IFace\View\WrapperView;
use CSS;
use JS;
use Link;
use Meta;
use Twig;

/**
 * Class WrapperView
 *
 * View wrapper
 *
 * @package BetaKiller
 * @author Spotman
 */
class WrapperViewTwig extends WrapperView
{
    protected function viewFactory($view_path)
    {
        // Using Twig instead of View
        return Twig::factory($view_path);
    }

    protected function getViewPath($codename)
    {
        // Using aliases in Twig
        return '@' . parent::getViewPath($codename);
    }

    // TODO move to BetaKiller_Twig_Extension
    protected function getData()
    {
        return [
            'meta_tags' => Meta::instance()->render(),
            'js_all'    => JS::instance()->get_all(),
            'css_all'   => CSS::instance()->get_all(),
            'links_all' => Link::instance()->render(),
        ];
    }
}
