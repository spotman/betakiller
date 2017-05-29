<?php defined('SYSPATH') OR die('No direct script access.');

use BetaKiller\Error\PhpExceptionStorageInterface;
use BetaKiller\Helper\AppEnv;
use BetaKiller\Helper\NotificationHelper;

class Log_PhpExceptionStorage extends Log_Writer
{
    /**
     * @var \BetaKiller\Error\PhpExceptionStorageInterface
     */
    protected $storage;

    /**
     * @var \BetaKiller\Helper\AppEnv
     */
    private $appEnv;

    /**
     * @var \BetaKiller\Helper\NotificationHelper
     */
    private $notificationHelper;

    /**
     * Log_PhpExceptionStorage constructor.
     *
     * @param \BetaKiller\Error\PhpExceptionStorageInterface $storage
     * @param \BetaKiller\Helper\AppEnv                      $env
     * @param \BetaKiller\Helper\NotificationHelper          $notificationHelper
     */
    public function __construct(PhpExceptionStorageInterface $storage, AppEnv $env, NotificationHelper $notificationHelper)
    {
        $this->storage            = $storage;
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
            } catch (\Exception $e) {
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

    protected function notify_developers_about_failure(\Exception $exception)
    {
        try {
            $message = $this->notificationHelper->createMessage();

            $this->notificationHelper->toDevelopers($message);

            $message
                ->setSubj('BetaKiller logging subsystem failure')
                ->setTemplateName('developer/error/subsystem-failure')
                ->setTemplateData([
                    'url'     => \Kohana::$base_url,
                    'message' => \Kohana_Exception::text($exception),
                ])
                ->send();
        } catch (\Exception $e) {
            // Silently fail
        }
    }

    protected function write_message(array $msg)
    {
        /** @var Exception|Kohana_Exception|null $exception */
        $exception = isset($msg['additional']['exception']) ? $msg['additional']['exception'] : null;

        if (!$exception) {
            return;
        }

        $this->storage->storeException($exception);
    }
}
