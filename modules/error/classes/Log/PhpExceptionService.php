<?php

use BetaKiller\Error\PhpExceptionService;
use BetaKiller\Helper\AppEnv;
use BetaKiller\Helper\NotificationHelper;

class Log_PhpExceptionService extends Log_Writer
{
    /**
     * @var \BetaKiller\Error\PhpExceptionService
     */
    protected $service;

    /**
     * @var \BetaKiller\Helper\AppEnv
     */
    private $appEnv;

    /**
     * @var \BetaKiller\Helper\NotificationHelper
     */
    private $notificationHelper;

    /**
     * Log_PhpExceptionService constructor.
     *
     * @param \BetaKiller\Error\PhpExceptionService $service
     * @param \BetaKiller\Helper\AppEnv                      $env
     * @param \BetaKiller\Helper\NotificationHelper          $notificationHelper
     */
    public function __construct(
        PhpExceptionService $service,
        AppEnv $env,
        NotificationHelper $notificationHelper
    ) {
        $this->service            = $service;
        $this->appEnv             = $env;
        $this->notificationHelper = $notificationHelper;
    }

    public function register()
    {
        if (!$this->appEnv->inProduction(true)) {
            return;
        }

        Kohana::$log->attach($this, Log::NOTICE, Log::EMERGENCY);
    }

    /**
     * Write an array of messages.
     *
     *     $writer->write($messages);
     *
     * @param   array $messages
     *
     * @return  void
     */
    public function write(array $messages)
    {
        foreach ($messages as $message) {
            try {
                // Write each message into the log
                $this->write_message($message);
            } catch (\Throwable $e) {
                // Prevent logging recursion
                Kohana::$log->detach($this);

                if ($this->appEnv->inProduction(true)) {
                    // Try to send notification to developers about logging subsystem failure
                    $this->notify_developers_about_failure($e);
                } else {
                    die(\Kohana_Exception::response($e));
                }

                // Stop executing
                break;
            }
        }
    }

    protected function notify_developers_about_failure(\Throwable $exception)
    {
        try {
            $message = $this->notificationHelper->createMessage();

            $this->notificationHelper->toDevelopers($message);

            $message
                ->setSubj('BetaKiller logging subsystem failure')
                ->setTemplateName('developer/error/subsystem-failure')
                ->setTemplateData([
                    'url'        => \Kohana::$base_url,
                    'message'    => \Kohana_Exception::text($exception),
                    'stacktrace' => $exception->getTraceAsString(),
                ])
                ->send();
        } catch (\Throwable $ignored) {
            // Fail silently
            error_log($ignored->getMessage().PHP_EOL.$ignored->getTraceAsString());
        }
    }

    protected function write_message(array $msg)
    {
        /** @var Exception|Kohana_Exception|null $exception */
        $exception = $msg['additional']['exception'] ?? null;

        if (!$exception) {
            return;
        }

        $this->service->storeException($exception);
    }
}
