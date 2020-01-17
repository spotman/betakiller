<?php
namespace BetaKiller\Log;

use Joli\JoliNotif\Notification;
use Joli\JoliNotif\Notifier;
use Joli\JoliNotif\NotifierFactory;
use Monolog\Handler\AbstractHandler;

final class DesktopNotificationHandler extends AbstractHandler
{
    /**
     * @var \Joli\JoliNotif\Notifier
     */
    private $notifier;

    public function __construct()
    {
        $this->notifier = NotifierFactory::createOrThrowException();

        parent::__construct();
    }

    public static function isSupported(): bool
    {
        return \interface_exists(Notifier::class);
    }

    /**
     * Handles a record.
     *
     * All records may be passed to this method, and the handler should discard
     * those that it does not want to handle.
     *
     * The return value of this function controls the bubbling process of the handler stack.
     * Unless the bubbling is interrupted (by returning true), the Logger class will keep on
     * calling further handlers in the stack with a given log record.
     *
     * @param array $record The record to handle
     *
     * @return bool true means that this handler handled the record, and that bubbling is not permitted.
     *                        false means the record was either not processed or that this handler allows bubbling.
     */
    public function handle(array $record): bool
    {
        /** @var \Throwable|null $exception */
        $exception = $record['context'][Logger::CONTEXT_KEY_EXCEPTION] ?? null;

        if ($exception) {
            // Find root exception
            while ($exception->getPrevious()) {
                $exception = $exception->getPrevious();
            }

            $notification = (new Notification())
                ->setTitle($exception->getMessage())
                ->setBody($exception->getTraceAsString());

            return $this->notifier->send($notification);
        }

        return false;
    }
}
