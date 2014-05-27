<?php defined('SYSPATH') OR die('No direct script access.');

class Controller_RobotsTXT extends Controller
{
    public function action_index()
    {
        header('Content-type: text/plain');

        echo ( Kohana::$environment == Kohana::PRODUCTION )
            ? $this->get_regular_content()
            : $this->get_denied_content();

        exit();
    }

    protected function get_regular_content()
    {
        return View::factory('robots.txt')->render();
    }

    protected function get_denied_content()
    {
        return  'User-agent: *'.
                "\r\n".
                'Disallow: /';
    }
}