<?php defined('SYSPATH') OR die('No direct script access.');


abstract class Task_Worker_Gearman extends Minion_Task {

    /**
     * @return array <key> => <method name>
     */
    abstract protected function get_worker_methods();

    /**
     * @return bool
     */
    protected function restart_after_method_call()
    {
        return FALSE;
    }

    protected function _execute(array $params)
    {
        $worker= new GearmanWorker();

        // TODO Get hosts/ports from config
        $worker->addServer('127.0.0.1');

        $methods = $this->get_worker_methods();

        if ( !$methods )
            throw new Minion_Exception('No methods defined for worker '.get_class($this));

        $task = $this;

        // Register methods
        foreach ( $methods as $key => $method )
        {
            $this->info('Binding method ['.$method.'] to key ['.$key.']');

            // Add methods
            $worker->addFunction($key, function(GearmanJob $job) use ($task, $method) {
                try
                {
                    $result = $task->$method($job);

                    // Force "complete" state coz no exception was thrown
                    $job->sendComplete( (string)$result );
                }
                catch ( Exception $e )
                {
                    // Log exception
                    Log::exception($e);

                    // Notify client
                    $job->sendData($e->getMessage());
                    $job->sendFail();
                }

                if ( $task->restart_after_method_call() )
                {
                    $this->info('Restarting worker after method call');
                    exit(0);
                }
            });
        }

        $this->info('Worker started!');

        // Loop
        while($worker->work())
        {
            if ($worker->returnCode() != GEARMAN_SUCCESS)
            {
                // For correct Gearman queue processing
                // http://hermanradtke.com/2011/04/11/retrying-failed-gearman-jobs.html
                exit(255);
            }
        }
    }
}
