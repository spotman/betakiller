<?php defined('SYSPATH') OR die('No direct script access.');

use BetaKiller\Notification\NotificationMessageCommon;
use BetaKiller\Helper\AppEnvTrait;

class Log_PhpExceptionStorage extends Log_Writer
{
    use AppEnvTrait;

    /**
     * @var \BetaKiller\Error\PhpExceptionStorageInterface
     */
    protected $storage;

    /**
     * Log_MongoDB constructor.
     */
    public function __construct()
    {
        $this->storage = new \BetaKiller\Error\PhpExceptionStorage();
    }

    /**
     * Write an array of messages.
     *
     *     $writer->write($messages);
     *
     * @param   array $messages
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

                if ($this->in_production(true)) {
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
            NotificationMessageCommon::instance()
                ->set_subj('BetaKiller logging subsystem failure')
                ->set_template_name('developer/error/subsystem-failure')
                ->set_template_data([
                    'url' => \Kohana::$base_url,
                    'message' => \Kohana_Exception::text($exception)
                ])
                ->to_developers()
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
