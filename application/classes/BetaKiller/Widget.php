<?php defined('SYSPATH') OR die('No direct script access.');

use BetaKiller\Widget;

abstract class BetaKiller_Widget extends Core_Widget {

    use BetaKiller\Helper\Base;

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

    /**
     * @param string    $message
     * @param array     $variables
     * @throws \BetaKiller\Widget\Exception
     */
    protected function throw_exception($message, array $variables = [])
    {
        throw new Widget\Exception($message, $variables);
    }
}
