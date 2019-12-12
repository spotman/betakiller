<?php
namespace BetaKiller\Error;

use BetaKiller\Factory\EntityFactoryInterface;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Log\Logger;
use BetaKiller\Model\LanguageInterface;
use BetaKiller\Model\PhpException;
use BetaKiller\Model\PhpExceptionModelInterface;
use BetaKiller\Notification\MessageTargetInterface;
use BetaKiller\Repository\PhpExceptionRepositoryInterface;
use Debug;
use Email;
use ErrorException;
use Monolog\Handler\AbstractProcessingHandler;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Throwable;

/**
 * Class PhpExceptionStorageHandler
 *
 * @package BetaKiller\Error
 */
class PhpExceptionStorageHandler extends AbstractProcessingHandler
{
    public const MIN_LEVEL = \Monolog\Logger::WARNING;

    public const NOTIFICATION_SUBSYSTEM_FAILURE = 'developer/error/subsystem-failure';

    private const REWRITE_KEYS = [
        'MySQL server has gone away',
        'Error while sending QUERY packet',
    ];

    private const IGNORE_KEYS = [
        'Server shutdown in progress',
    ];

    /**
     * Notify not faster than 1 message in T seconds
     */
    private const REPEAT_DELAY = 300;

    /**
     * @var bool
     */
    private $enabled = true;

    /**
     * @var \BetaKiller\Factory\EntityFactoryInterface
     */
    private $entityFactory;

    /**
     * @var \BetaKiller\Repository\PhpExceptionRepository
     */
    private $repository;

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
     * @param \BetaKiller\Factory\EntityFactoryInterface             $entityFactory
     * @param \BetaKiller\Repository\PhpExceptionRepositoryInterface $repository
     * @param \BetaKiller\Helper\NotificationHelper                  $notificationHelper
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        PhpExceptionRepositoryInterface $repository,
        NotificationHelper $notificationHelper
    ) {
        $this->entityFactory = $entityFactory;
        $this->repository    = $repository;
        $this->notification  = $notificationHelper;

        parent::__construct(self::MIN_LEVEL);
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param array $record
     *
     * @return void
     */
    protected function write(array $record): void
    {
        if (!$this->enabled) {
            return;
        }

        $exception = $this->detectException($record);

        /** @var ServerRequestInterface|null $request */
        $request = $record['context'][Logger::CONTEXT_KEY_REQUEST] ?? null;

        try {
            $this->storeException($exception, $request);
        } catch (Throwable $subsystemException) {
            // Prevent logging recursion
            $this->enabled = false;

            $this->notifyDevelopersAboutFailure($subsystemException, $exception);
        }
    }

    private function detectException(array $record): Throwable
    {
        /** @var \Throwable|null $exception */
        $exception = $record['context'][Logger::CONTEXT_KEY_EXCEPTION] ?? null;

        if (!$exception) {
            $extra = $record['extra'] ?? [];

            // Create dummy exception if this is a plain "alert" or "emergency" message
            $exception = new ErrorException(
                (string)$record['message'],
                0,
                E_WARNING,
                $extra['file'] ?? 'Unknown',
                $extra['line'] ?? 0
            );
        }

        return $exception;
    }

    /**
     * @param \Throwable                                    $exception
     * @param null|\Psr\Http\Message\ServerRequestInterface $request Null in cli mode
     *
     * @return void
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \ReflectionException
     */
    private function storeException(Throwable $exception, ?ServerRequestInterface $request): void
    {
        $class = (new ReflectionClass($exception))->getShortName();
        $code  = $exception->getCode();
        $file  = $exception->getFile();
        $line  = $exception->getLine();

        // Combine message
        $message = "[$code] $class: ".$exception->getMessage();

        // Ignore several messages messages
        foreach (self::IGNORE_KEYS as $key) {
            if (mb_strpos($message, $key) !== false) {
                return;
            }
        }

        // Prevent duplicating of similar messages
        foreach (self::REWRITE_KEYS as $key) {
            if (mb_strpos($message, $key) !== false) {
                $message = $key;
                break;
            }
        }

        // Getting unique hash for current message
        $hash = $this->makeHashFor($message);

        // Searching for existing exception
        $model = $this->repository->findByHash($hash);

        // Fetching user if exists
        $user = ($request && ServerRequestHelper::hasUser($request))
            ? ServerRequestHelper::getUser($request)
            : null;

        if ($model) {
            // Mark exception as repeated
            $model->markAsRepeated($user);
        } else {
            /** @var PhpExceptionModelInterface $model */
            $model = $this->entityFactory->create(PhpException::getModelName());
            $model
                ->setHash($hash)
                ->setMessage($message)
                ->markAsNew($user);
        }

        // Increase occurrence counter
        $model->incrementCounter();

        if ($request) {
            // Adding URL
            $url = ServerRequestHelper::getUrl($request);
            $model->addUrl($url);

            // Adding module name for grouping purposes
            $module = ServerRequestHelper::getModule($request);
            if ($module) {
                $model->addModule($module);
            }
        }

        // Adding error source file and line number
        $model->addPath($file.':'.$line);

        if ($exception) {
            // Getting HTML stacktrace
            $stacktrace = Debug::htmlStacktrace($exception, $request);

            // Adding trace
            $model->setTrace($stacktrace);
        }

        $isNotificationNeeded = $this->isNotificationNeededFor($model);

        $this->logger->debug('Notification needed is :value', [':value' => $isNotificationNeeded ? 'true' : 'false']);

        if ($isNotificationNeeded) {
            $model->notificationRequired();
        }

        // Saving
        $this->repository->save($model);

        $this->logger->debug('Exception stored with ID :id', [':id' => $model->getID()]);
    }

    protected function makeHashFor($message)
    {
        return sha1($message);
    }

    /**
     * Returns TRUE if exception needs to be notified
     *
     * @param PhpExceptionModelInterface $model
     *
     * @return bool
     */
    public function isNotificationNeededFor(PhpExceptionModelInterface $model): bool
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
        return !$lastNotifiedAtTimestamp || $timeDiffInSeconds > static::REPEAT_DELAY;
    }

    public static function getNotificationTarget(NotificationHelper $helper): MessageTargetInterface
    {
        return $helper->emailTarget(
            getenv('DEBUG_EMAIL_ADDRESS'),
            'Bug Hunters',
            LanguageInterface::ISO_EN // Only English template is available for now
        );
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

    /**
     * @param \Throwable $subsystemException
     * @param \Throwable $originalException
     *
     * @throws \BetaKiller\Notification\NotificationException
     */
    private function sendNotification(Throwable $subsystemException, Throwable $originalException): void
    {
        $target = self::getNotificationTarget($this->notification);

        $this->notification->directMessage(self::NOTIFICATION_SUBSYSTEM_FAILURE, $target, [
            'url'       => getenv('APP_URL'),
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

    private function getExceptionText(Throwable $e): string
    {
        return sprintf('%s [ %s ]: %s ~ %s [ %d ]',
            get_class($e), $e->getCode(), strip_tags($e->getMessage()), Debug::path($e->getFile()), $e->getLine());
    }

    private function sendPlainEmail(Throwable $notificationX, Throwable $subsystemX, Throwable $originalX): void
    {
        try {
            $message = '';

            foreach ([$notificationX, $subsystemX, $originalX] as $e) {
                $message .= $this->getExceptionText($e).PHP_EOL.$e->getTraceAsString().PHP_EOL.PHP_EOL;
            }

            // Send plain message
            Email::send(
                null,
                getenv('DEBUG_EMAIL_ADDRESS'),
                'Exception handling error',
                nl2br($message),
                true
            );
        } catch (Throwable $ignored) {
            // Nothing we can do here, store exceptions in a system log as a last resort
            $this->writeToErrorLog($originalX);
            $this->writeToErrorLog($subsystemX);
            $this->writeToErrorLog($notificationX);
            $this->writeToErrorLog($ignored);
        }
    }

    private function writeToErrorLog(Throwable $e): void
    {
        /** @noinspection ForgottenDebugOutputInspection */
        error_log($e->getMessage().PHP_EOL.$e->getTraceAsString());
    }
}
