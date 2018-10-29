<?php
namespace BetaKiller\Error;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Exception;
use BetaKiller\ExceptionInterface;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Model\PhpExceptionModelInterface;
use BetaKiller\Repository\PhpExceptionRepository;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Class PhpExceptionStorageHandler
 *
 * @package BetaKiller\Error
 */
class PhpExceptionStorageHandler extends AbstractProcessingHandler
{
    public const MIN_LEVEL = Logger::ERROR;

    public const NOTIFICATION_SUBSYSTEM_FAILURE = 'developer/error/subsystem-failure';

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
    private $notification;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * PhpExceptionStorageHandler constructor.
     *
     * @param \BetaKiller\Repository\PhpExceptionRepository $repository
     * @param \BetaKiller\Config\AppConfigInterface         $appConfig
     * @param \BetaKiller\Helper\NotificationHelper         $notificationHelper
     */
    public function __construct(
        PhpExceptionRepository $repository,
        AppConfigInterface $appConfig,
        NotificationHelper $notificationHelper
    ) {
        $this->repository   = $repository;
        $this->appConfig    = $appConfig;
        $this->notification = $notificationHelper;

        parent::__construct(self::MIN_LEVEL);
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
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
            // Create dummy exception if this is a plain "alert" or "emergency" message
            $exception = new Exception((string)$record['formatted']);
        }

        try {
            // TODO Extract this call from here and move it to a separate class, called in the error page middleware
            $this->storeException($exception, null);
        } catch (\Throwable $subsystemException) {
            // Prevent logging recursion
            $this->enabled = false;

            $this->notifyDevelopersAboutFailure($subsystemException, $exception);
        }
    }

    /**
     * @param \Throwable                                    $exception
     * @param null|\Psr\Http\Message\ServerRequestInterface $request Null in cli mode
     *
     * @return \BetaKiller\Model\PhpExceptionModelInterface|null
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \Kohana_Exception
     */
    public function storeException(\Throwable $exception, ?ServerRequestInterface $request): ?PhpExceptionModelInterface
    {
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

        // Fetching user if exists
        $user = ($request && ServerRequestHelper::hasUser($request))
            ? ServerRequestHelper::getUser($request)
            : null;

        $currentTime = new \DateTime;

        if ($model) {
            // Mark exception as repeated
            $model
                ->markAsRepeated($user)
                ->setLastSeenAt($currentTime);
        } else {
            $model = $this->repository->create()
                ->setHash($hash)
                ->setCreatedAt($currentTime)
                ->setLastSeenAt($currentTime)
                ->setMessage($message)
                ->markAsNew($user);
        }

        // Increase occurrence counter
        $model->incrementCounter();

        // Trying to get current URL
        $url = $request ? ServerRequestHelper::getUrl($request) : null;

        // Adding URL
        if ($url) {
            $model->addUrl($url);
        }

        // Adding error source file and line number
        $model->addPath($file.':'.$line);

        if ($exception) {
            // Getting HTML stacktrace
            $stacktrace = \Debug::htmlStacktrace($exception);

            // Adding trace
            $model->setTrace($stacktrace);
        }

        // Trying to get current module
        $module = $request ? ServerRequestHelper::getModule($request) : null;

        // Adding module name for grouping purposes
        if ($module) {
            $model->addModule($module);
        }

        $isNotificationNeeded = $this->isNotificationNeededFor($model, static::REPEAT_COUNT, static::REPEAT_DELAY);

        $this->logger->debug('Notification needed is :value', [':value' => $isNotificationNeeded ? 'true' : 'false']);

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
    public function isNotificationNeededFor(PhpExceptionModelInterface $model, int $repeatCount, int $repeatDelay): bool
    {
        // Skip ignored exceptions
        if ($model->isIgnored()) {
            $this->logger->debug('Ignored exception :message', [':message' => $model->getMessage()]);

            return false;
        }

        $lastSeenAtTimestamp     = $model->getLastSeenAt()->getTimestamp();
        $lastNotifiedAt          = $model->getLastNotifiedAt();
        $lastNotifiedAtTimestamp = $lastNotifiedAt ? $lastNotifiedAt->getTimestamp() : 0;

        $timeDiffInSeconds = $lastSeenAtTimestamp - $lastNotifiedAtTimestamp;

        // Throttle by time
        if ($lastNotifiedAtTimestamp && ($timeDiffInSeconds < $repeatDelay)) {
            return false;
        }

        // New error needs to be notified only once
        if (!$lastNotifiedAtTimestamp && $model->isNew()) {
            $this->logger->debug('New exception needs to be notified');

            return true;
        }

        // Repeated error needs to be notified
        if ($model->isRepeated()) {
            $this->logger->debug('Repeated exception needs to be notified');

            return true;
        }

        $this->logger->debug('Total exception counter is :value', [':value' => $model->getCounter()]);

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
     */
    private function sendNotification(\Throwable $subsystemException, \Throwable $originalException): void
    {
        $this->notification->groupMessage(self::NOTIFICATION_SUBSYSTEM_FAILURE, [
            'url'       => (string)$this->appConfig->getBaseUri(),
            'subsystem' => [
                'message'    => $this->getExceptionText($subsystemException),
                'stacktrace' => $subsystemException->getTraceAsString(),
            ],
            'original'  => [
                'message'    => $this->getExceptionText($originalException),
                'stacktrace' => $originalException->getTraceAsString(),
            ],
        ]);
    }

    private function getExceptionText(\Throwable $e): string
    {
        return sprintf('%s [ %s ]: %s ~ %s [ %d ]',
            \get_class($e), $e->getCode(), strip_tags($e->getMessage()), \Debug::path($e->getFile()), $e->getLine());
    }

    private function sendPlainEmail(\Throwable $notificationX, \Throwable $subsystemX, \Throwable $originalX): void
    {
        try {
            $message = '';

            foreach ([$notificationX, $subsystemX, $originalX] as $e) {
                $message .= $this->getExceptionText($e).PHP_EOL.$e->getTraceAsString().PHP_EOL.PHP_EOL;
            }

            // Send plain message
            mail($this->appConfig->getAdminEmail(), 'Exception handling error', nl2br($message));
        } catch (\Throwable $ignored) {
            // Nothing we can do here, store exceptions in a system log as a last resort
            $this->writeToErrorLog($originalX);
            $this->writeToErrorLog($subsystemX);
            $this->writeToErrorLog($notificationX);
            $this->writeToErrorLog($ignored);
        }
    }

    private function writeToErrorLog(\Throwable $e): void
    {
        /** @noinspection ForgottenDebugOutputInspection */
        error_log($e->getMessage().PHP_EOL.$e->getTraceAsString());
    }
}
