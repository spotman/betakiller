<?php
namespace BetaKiller\Error;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\ExceptionInterface;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Model\PhpExceptionModelInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\PhpExceptionRepository;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Psr\Log\LoggerAwareTrait;

/**
 * Class PhpExceptionStorageHandler
 *
 * @package BetaKiller\Error
 */
class PhpExceptionStorageHandler extends AbstractProcessingHandler
{
    use LoggerAwareTrait;

    public const MIN_LEVEL = Logger::ERROR;

    /**
     * Notify about N-th duplicated exception only
     */
    private const REPEAT_COUNT = 50;

    /**
     * Notify not faster than 1 message in T seconds
     */
    private const REPEAT_DELAY = 600;

    /**
     * @var bool
     */
    private $enabled = true;

    /**
     * @var \BetaKiller\Repository\PhpExceptionRepository
     */
    private $repository;

    /**
     * @var \BetaKiller\Config\AppConfigInterface
     */
    private $appConfig;

    /**
     * @var \BetaKiller\Helper\NotificationHelper
     */
    private $notificationHelper;

    /**
     * @var \BetaKiller\Model\UserInterface;
     */
    private $user;

    /**
     * PhpExceptionStorageHandler constructor.
     *
     * @param \BetaKiller\Repository\PhpExceptionRepository $repository
     * @param \BetaKiller\Model\UserInterface               $user
     * @param \BetaKiller\Config\AppConfigInterface         $appConfig
     * @param \BetaKiller\Helper\NotificationHelper         $notificationHelper
     */
    public function __construct(
        PhpExceptionRepository $repository,
        UserInterface $user,
        AppConfigInterface $appConfig,
        NotificationHelper $notificationHelper
    ) {
        $this->repository         = $repository;
        $this->user               = $user;
        $this->appConfig          = $appConfig;
        $this->notificationHelper = $notificationHelper;

        parent::__construct(self::MIN_LEVEL);
    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  array $record
     *
     * @return void
     */
    protected function write(array $record): void
    {
        if (!$this->enabled) {
            return;
        }

        /** @var \Throwable|null $exception */
        $exception = $record['context']['exception'] ?? null;

        if (!$exception) {
            return;
        }

        try {
            $this->storeException($exception);
        } catch (\Throwable $subsystemException) {
            // Prevent logging recursion
            $this->enabled = false;

            $this->notifyDevelopersAboutFailure($subsystemException, $exception);
        }
    }

    /**
     * @param \Throwable $exception
     *
     * @return \BetaKiller\Model\PhpExceptionModelInterface|null
     * @throws \ORM_Validation_Exception
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \Kohana_Exception
     */
    public function storeException(\Throwable $exception): ?PhpExceptionModelInterface
    {
        $user = $this->user;

        if ($exception instanceof ExceptionInterface && !$exception->isNotificationEnabled()) {
            return null;
        }

        $class = \get_class($exception);
        $code  = $exception->getCode();
        $file  = $exception->getFile();
        $line  = $exception->getLine();

        // Combine message and escape symbols to minimize XSS
        $message = \HTML::chars("[$code] $class: ".$exception->getMessage());

        // Getting unique hash for current message
        $hash = $this->makeHashFor($message);

        // Searching for existing exception
        $model = $this->repository->findByHash($hash);

        $currentTime = new \DateTime;

        if ($model) {
            // Mark exception as repeated
            $model->markAsRepeated($user);
        } else {
            $model = $this->repository->create()
                ->setHash($hash)
                ->setCreatedAt($currentTime)
                ->setMessage($message)
                ->markAsNew($user);
        }

        $model->setLastSeenAt($currentTime);

        // Increase occurrence counter
        $model->incrementCounter();

        /** @var \Request|null $request */
        $request = \Request::current() ?: null;

        // Trying to get current URL
        $url = $request ? $request::detect_uri() : null;

        // Adding URL
        if ($url) {
            $model->addUrl($url);
        }

        // Adding error source file and line number
        $model->addPath($file.':'.$line);

        if ($exception) {
            // Getting HTML stacktrace
            $eResponse = \Kohana_Exception::response($exception);

            // Adding trace
            $model->setTrace((string)$eResponse);
        }

        // Trying to get current module
        $module = $request ? $request->module() : null;

        // Adding module name for grouping purposes
        if ($module) {
            $model->addModule($module);
        }

        $isNotificationNeeded = $this->isNotificationNeededFor($model, static::REPEAT_COUNT, static::REPEAT_DELAY);

//        $this->logger->debug('Notification needed is :value', [':value' => $isNotificationNeeded ? 'true' : 'false']);

        if ($isNotificationNeeded) {
            $model->notificationRequired();
        }

        // Saving
        $this->repository->save($model);

        return $model;
    }

    protected function makeHashFor($message)
    {
        return sha1($message);
    }

    /**
     * Returns TRUE if exception needs to be notified
     *
     * @param PhpExceptionModelInterface $model
     * @param int                        $repeatCount
     * @param int                        $repeatDelay
     *
     * @return bool
     */
    public function isNotificationNeededFor(PhpExceptionModelInterface $model, $repeatCount, $repeatDelay): bool
    {
        // Skip ignored exceptions
        if ($model->isIgnored()) {
//            $this->logger->debug('Ignored exception');

            return false;
        }

        $lastSeenAt              = $model->getLastSeenAt();
        $lastSeenAtTimestamp     = $lastSeenAt->getTimestamp();
        $lastNotifiedAt          = $model->getLastNotifiedAt();
        $lastNotifiedAtTimestamp = $lastNotifiedAt ? $lastNotifiedAt->getTimestamp() : 0;

        $timeDiffInSeconds = $lastSeenAtTimestamp - $lastNotifiedAtTimestamp;

//        $this->logger->debug('Time diff between :last and :seen is :diff', [
//            ':last' => $lastNotifiedAtTimestamp,
//            ':seen' => $lastSeenAtTimestamp,
//            ':diff' => $timeDiffInSeconds,
//        ]);

        // Throttle by time
        if ($lastNotifiedAtTimestamp && ($timeDiffInSeconds < $repeatDelay)) {
            return false;
        }

        // New error needs to be notified only once
        if (!$lastNotifiedAtTimestamp && $model->isNew()) {
//            $this->logger->debug('New exception needs to be notified');

            return true;
        }

        // Repeated error needs to be notified
        if ($model->isRepeated()) {
//            $this->logger->debug('Repeated exception needs to be notified');

            return true;
        }

//        $this->logger->debug('Total counter is :value', [':value' => $model->getCounter()]);

        // Throttle by occurrence number
        return ($model->getCounter() % $repeatCount === 1);
    }

    private function notifyDevelopersAboutFailure(\Throwable $subsystemException, \Throwable $originalException): void
    {
        // Try to send notification to developers about logging subsystem failure
        try {
            $this->sendNotification($subsystemException, $originalException);
        } catch (\Throwable $notificationException) {
            $this->sendPlainEmail($notificationException, $subsystemException, $originalException);
        }
    }

    /**
     * @param \Throwable $subsystemException
     * @param \Throwable $originalException
     *
     * @throws \BetaKiller\Notification\NotificationException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    private function sendNotification(\Throwable $subsystemException, \Throwable $originalException): void
    {
        $message = $this->notificationHelper->createMessage();

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
            ]);

        $this->notificationHelper
            ->toDevelopers($message)
            ->send($message);
    }

    private function getExceptionText(\Throwable $e): string
    {
        return sprintf('%s [ %s ]: %s ~ %s [ %d ]',
            \get_class($e), $e->getCode(), strip_tags($e->getMessage()), \Debug::path($e->getFile()), $e->getLine());
    }

    private function sendPlainEmail(\Throwable $notificationX, \Throwable $subsystemX, \Throwable $originalX)
    {
        try {
            $message = '';

            foreach ([$notificationX, $subsystemX, $originalX] as $e) {
                $message .= $this->getExceptionText($e).PHP_EOL.$e->getTraceAsString().PHP_EOL.PHP_EOL;
            }

            // Send plain message
            mail($this->appConfig->getAdminEmail(), 'Exception handling error', nl2br($message));
        } catch (\Throwable $ignored) {
            // Nothing we can do here, store exception in a system log as a last resort

            /** @noinspection ForgottenDebugOutputInspection */
            error_log($ignored->getMessage().PHP_EOL.$ignored->getTraceAsString());
        }
    }
}
