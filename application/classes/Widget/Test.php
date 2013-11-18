<?php defined('SYSPATH') OR die('No direct script access.');

class Widget_Test extends Widget {

    public function action_test()
    {
//        Profiler::enable();

        $this->response()->content_type(Response::JSON);

//        Response::current()->wrapper();
//
//        Response::current()->body('asdasdasd');
//
//        Response::current()->send_json();
//
//        $this->response()->send_json();

        $result = API::user()->register('a','s');

        //$this->response()->send_json(Response::JSON_SUCCESS, $result);
    }

}