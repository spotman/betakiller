<?php defined('SYSPATH') OR die('No direct script access.');

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

}
