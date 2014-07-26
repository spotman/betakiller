<?php defined('SYSPATH') OR die('No direct script access.');

class Controller_Deployer extends Controller_Developer {

    public function action_index()
    {
        $view = $this->view('index');

        $this->send_view($view);
    }

    public function action_execute()
    {
        $command = $this->request()->param('command');
        $this->execute_command($command);
    }

    protected function execute_command($action)
    {
        $this->_layout = NULL;

        set_time_limit(0);

        // Recommended to prevent caching of event data
        header('Cache-Control: no-cache');
//        header('Content-Type: text/html');
        header('Content-Type: text/octet-stream');

        // Implicitly flush the buffer(s)
        ini_set('implicit_flush', true);
        ob_implicit_flush(true);
        ob_end_flush();

        $path = MultiSite::instance()->site_path();

        $command = "cd $path && dep $action --no-interaction --ansi";

//        echo 'running command: '.$command."\r\n\r\n";
//        flush();

        $handler = popen($command.' 2>&1', 'r');

        while ( !feof($handler) )
        {
            $buffer = fgets($handler);

            echo $buffer;
            flush();

            usleep(50000);
        }

        pclose($handler);

        exit();
    }

}