<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Task_Worker_Gearman extends \BetaKiller\Task\AbstractTask
{
    /**
     * @Inject
     * @var \BetaKiller\Log\LoggerHelper
     */
    private $loggerHelper;

    /**
     * @return array <key> => <method name>
     */
    abstract protected function getWorkerMethods();

    /**
     * @return bool
     */
    protected function restartAfterMethodCall()
    {
        return false;
    }

    protected function _execute(array $params)
    {
        $worker = new GearmanWorker();

        // TODO Get hosts/ports from config
        $worker->addServer();

        $methods = $this->getWorkerMethods();

        if (!$methods) {
            throw new Minion_Exception('No methods defined for worker '.get_class($this));
        }

        $task = $this;

        // Register methods
        foreach ($methods as $key => $method) {
            $this->logger->info('Binding method ['.$method.'] to key ['.$key.']');

            // Add methods
            $worker->addFunction($key, function (GearmanJob $job) use ($task, $method) {
                try {
                    $result = $task->$method($job);

                    // Force "complete" state coz no exception was thrown
                    $job->sendComplete((string)$result);
                } catch (Throwable $e) {
                    // Log exception
                    $this->loggerHelper->logException($e);

                    // Notify client
                    $job->sendData($e->getMessage());
                    $job->sendFail();
                }

                if ($task->restartAfterMethodCall()) {
                    $this->logger->info('Restarting worker after method call');
                    exit(0);
                }
            });
        }

        $this->logger->info('Worker started!');

        // Loop
        while ($worker->work()) {
            if ($worker->returnCode() !== GEARMAN_SUCCESS) {
                // For correct Gearman queue processing
                // http://hermanradtke.com/2011/04/11/retrying-failed-gearman-jobs.html
                exit(255);
            }
        }
    }
}
