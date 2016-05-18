<?php defined('SYSPATH') OR die('No direct script access.');

class Controller_Sitemap extends Controller
{
    public function action_index()
    {
        Service_Sitemap::instance()
            ->generate()
            ->serve($this->response());
    }
}
