<?php
namespace BetaKiller\View;

use CSS;
use JS;
use Link;
use Meta;
use Twig;
use View_Wrapper;

/**
 * Class View_Wrapper
 *
 * View wrapper
 *
 * @package BetaKiller
 * @author Spotman
 */
class ViewWrapperTwig extends View_Wrapper
{
    protected function view_factory($view_path)
    {
        // Using Twig instead of View
        return Twig::factory($view_path);
    }

    protected function get_view_path($codename)
    {
        // Using aliases in Twig
        return '@' . parent::get_view_path($codename);
    }

    // TODO move to BetaKiller_Twig_Extension
    protected function get_data()
    {
        return [
            'meta_tags' => Meta::instance()->render(),
            'js_all'    => JS::instance()->get_all(),
            'css_all'   => CSS::instance()->get_all(),
            'links_all' => Link::instance()->render(),
        ];
    }
}
