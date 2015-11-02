<?php defined('SYSPATH') OR die('No direct script access.');

use BetaKiller\Widget;

abstract class BetaKiller_Widget extends Core_Widget {

    /**
     * Returns Twig view instance
     *
     * @param null $file
     * @param array $data
     * @return Twig
     */
    protected function view_factory($file = NULL, array $data = NULL)
    {
        return Twig::factory($file, $data);
    }

    final protected function current_user($allow_guest = FALSE)
    {
        return Env::user($allow_guest);
    }

    final protected function url_parameters()
    {
        return Env::url_parameters();
    }

    final protected function url_dispatcher()
    {
        return Env::url_dispatcher();
    }

    /**
     * @param $codename
     * @return IFace
     */
    final protected function iface_factory($codename)
    {
        return IFace::by_codename($codename);
    }

    /**
     * @param string    $message
     * @param array     $variables
     * @throws \BetaKiller\Widget\Exception
     */
    protected function exception($message, array $variables = [])
    {
        throw new Widget\Exception($message, $variables);
    }
}
