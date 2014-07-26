<?php defined('SYSPATH') OR die('No direct script access.');

class Controller_Deployer extends Controller_Developer {

    public function action_index()
    {
        $view = $this->view('index');

//        $view->set('commands', json_encode($this->get_commands_list()));

        $this->send_view($view);
    }

    public function action_execute()
    {
        $command_name = $this->request()->param('command');
        $this->stream_command($command_name);
    }

//    protected function get_commands_list()
//    {
//        ob_start();
//        $this->execute_command('list --xml');
//        $raw = ob_get_clean();
//
//        $xml = simplexml_load_string($raw);
//
//        $data = [];
//
//        foreach ( $xml->commands->children() as $command )
//        {
//            $data[] = (string) $command->attributes()->name;
//        }
//
//        return $data;
//    }

    protected function stream_command($name)
    {
        $this->_layout = NULL;

        // Recommended to prevent caching of event data
        header('Cache-Control: no-cache');
        header('Content-Type: text/octet-stream');

        // Implicitly flush the buffer(s)
        ini_set('implicit_flush', true);
        ob_implicit_flush(true);
        ob_end_flush();

        $this->execute_command($name);

        exit();
    }

    protected function execute_command($name, $delay = TRUE)
    {
        $path = MultiSite::instance()->site_path();

        $cmd = "cd $path && dep $name --no-interaction --ansi";

//        echo 'running command: '.$cmd."\r\n\r\n";
//        flush();

        set_time_limit(0);
        ignore_user_abort(TRUE);

        $handler = popen($cmd.' 2>&1', 'r');

        while ( !feof($handler) )
        {
            $buffer = fgets($handler);

            echo $buffer;
            flush();

            if ( $delay )
                usleep(25000);

            if ( connection_aborted() )
            {
                // TODO Уничтожение дочернего процесса
                pclose($handler);
                exit();
            }
        }

        pclose($handler);
    }

}