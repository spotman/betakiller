<?php

namespace BetaKiller\Log;

use BetaKiller\Helper\LoggerHelper;
use Joli\JoliNotif\Notification;
use Joli\JoliNotif\Notifier;
use Joli\JoliNotif\NotifierFactory;
use Monolog\Handler\AbstractHandler;
use Monolog\LogRecord;

final class DesktopNotificationHandler extends AbstractHandler
{
    /**
     * @var \Joli\JoliNotif\Notifier
     */
    private Notifier $notifier;

    public function __construct()
    {
        $this->notifier = NotifierFactory::create();

        parent::__construct();
    }

    public static function isSupported(): bool
    {
        return \interface_exists(Notifier::class);
    }

    public function handle(LogRecord $record): bool
    {
        /** @var \Throwable|null $exception */
        $exception = $record['context'][LoggerHelper::CONTEXT_KEY_EXCEPTION] ?? null;

        if ($exception) {
            // Find root exception
            while ($exception->getPrevious()) {
                $exception = $exception->getPrevious();
            }

            $notification = (new Notification())
                ->setTitle($exception->getMessage())
                ->setBody($exception->getTraceAsString());

            $this->notifier->send($notification);
        }

        return false;
    }
}
