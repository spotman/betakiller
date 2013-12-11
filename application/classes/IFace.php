<?php defined('SYSPATH') OR die('No direct script access.');

abstract class IFace extends Kohana_IFace {

    protected function view_factory($path)
    {
        // Use Twig templates instead of Kohana views
        return Twig::factory($path);
    }

}
