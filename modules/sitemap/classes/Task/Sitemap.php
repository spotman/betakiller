<?php defined('SYSPATH') OR die('No direct script access.');

class Task_Sitemap extends Minion_Task
{
    protected function _execute(array $params)
    {
        Service_Sitemap::instance()->generate();
    }
}
