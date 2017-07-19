<?php

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Error\PhpExceptionService;
use BetaKiller\Helper\AppEnv;
use BetaKiller\Helper\NotificationHelper;

class Log_PhpExceptionService extends Log_Writer
{
    /**
     * @var \BetaKiller\Error\PhpExceptionService
     */
    private $service;

    /**
     * @var \BetaKiller\Helper\AppEnv
     */
    private $appEnv;

    /**
     * @var \BetaKiller\Config\AppConfigInterface
     */
    private $appConfig;

    /**
     * @var \BetaKiller\Helper\NotificationHelper
     */
    private $notificationHelper;

    /**
     * @var bool
     */
    private $enabled = true;

    /**
     * Log_PhpExceptionService constructor.
     *
     * @param \BetaKiller\Error\PhpExceptionService $service
     * @param \BetaKiller\Helper\AppEnv             $env
     * @param \BetaKiller\Config\AppConfigInterface $appConfig
     * @param \BetaKiller\Helper\NotificationHelper $notificationHelper
     */
    public function __construct(
        PhpExceptionService $service,
        AppEnv $env,
        AppConfigInterface $appConfig,
        NotificationHelper $notificationHelper
    ) {
        $this->service            = $service;
        $this->appEnv             = $env;
        $this->appConfig          = $appConfig;
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
    public function write(array $messages): void
    {
        foreach ($messages as $message) {
            // Write each message into the log
            $this->writeMessage($message);
        }
    }

    private function writeMessage(array $msg)
    {
        if (!$this->enabled) {
            return;
        }

        /** @var Exception|Kohana_Exception|null $exception */
        $exception = $msg['additional']['exception'] ?? null;

        if (!$exception) {
            return;
        }

        try {
            $this->service->storeException($exception);
        } catch (Throwable $subsystemException) {
            // Prevent logging recursion
            $this->enabled = false;

            $this->notifyDevelopersAboutFailure($subsystemException, $exception);
        }
    }

    private function notifyDevelopersAboutFailure(Throwable $subsystemException, Throwable $originalException): void
    {
        // Try to send notification to developers about logging subsystem failure
        try {
            $this->sendNotification($subsystemException, $originalException);
        } catch (Throwable $notificationException) {
            $this->sendPlainEmail($notificationException, $subsystemException, $originalException);
        }
    }

    private function sendNotification(Throwable $subsystemException, Throwable $originalException): void
    {
        $message = $this->notificationHelper->createMessage();

        $this->notificationHelper->toDevelopers($message);

        $message
            ->setSubj('BetaKiller logging subsystem failure')
            ->setTemplateName('developer/error/subsystem-failure')
            ->setTemplateData([
                'url'       => $this->appConfig->getBaseUrl(),
                'subsystem' => [
                    'message'    => $this->getExceptionText($subsystemException),
                    'stacktrace' => $subsystemException->getTraceAsString(),
                ],
                'original'  => [
                    'message'    => $this->getExceptionText($originalException),
                    'stacktrace' => $originalException->getTraceAsString(),
                ],
            ])
            ->send();
    }

    private function getExceptionText(Throwable $e): string
    {
        return sprintf('%s [ %s ]: %s ~ %s [ %d ]',
            get_class($e), $e->getCode(), strip_tags($e->getMessage()), Debug::path($e->getFile()), $e->getLine());
    }

    private function sendPlainEmail(Throwable $notificationX, Throwable $subsystemX, Throwable $originalX)
    {
        try {
            $message = '';

            foreach ([$notificationX, $subsystemX, $originalX] as $e) {
                $message .= $this->getExceptionText($e).PHP_EOL.$e->getTraceAsString().PHP_EOL.PHP_EOL;
            }

            // Send plain message
            mail($this->appConfig->getAdminEmail(), 'Exception handling error', nl2br($message));
        } catch (Throwable $ignored) {
            // Nothing we can do here, silently skip
        }
    }
}
