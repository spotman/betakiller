<?php
namespace BetaKiller\Error;

use BetaKiller\Helper\IFaceHelper;
use BetaKiller\Helper\LogTrait;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Model\IFaceZone;
use BetaKiller\Model\PhpExceptionModelInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\PhpExceptionRepository;

class PhpExceptionService
{
    use LogTrait;

    /**
     * Notify about N-th duplicated exception only
     */
    const REPEAT_COUNT = 50;

    /**
     * Notify not faster than 1 message in T seconds
     */
    const REPEAT_DELAY = 600;

    /**
     * @var \BetaKiller\Repository\PhpExceptionRepository
     */
    private $repository;

    /**
     * @var \BetaKiller\Helper\NotificationHelper
     */
    private $notificationHelper;

    /**
     * @var UserInterface;
     */
    private $user;

    /**
     * @var \BetaKiller\Helper\IFaceHelper
     */
    private $ifaceHelper;

    /**
     * PhpExceptionService constructor.
     *
     * @param \BetaKiller\Repository\PhpExceptionRepository $repository
     * @param \BetaKiller\Helper\NotificationHelper         $notificationHelper
     * @param \BetaKiller\Helper\IFaceHelper                $ifaceHelper
     * @param \BetaKiller\Model\UserInterface               $user
     */
    public function __construct(
        PhpExceptionRepository $repository,
        NotificationHelper $notificationHelper,
        IFaceHelper $ifaceHelper,
        UserInterface $user
    ) {
        $this->user               = $user;
        $this->repository         = $repository;
        $this->ifaceHelper        = $ifaceHelper;
        $this->notificationHelper = $notificationHelper;
    }

    /**
     * @param \Throwable $exception
     *
     * @return PhpExceptionModelInterface|null
     */
    public function storeException(\Throwable $exception): ?PhpExceptionModelInterface
    {
        $user = $this->user;

        if ($exception instanceof \BetaKiller_Kohana_Exception && !$exception->isNotificationEnabled()) {
            return null;
        }

        $class = get_class($exception);
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
            $model
                ->setLastSeenAt($currentTime)
                ->markAsRepeated($user);
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

        /** @var \Request $request */
        $request = \Request::current() ?: null;

        // Trying to get current URL
        $url = $request ? $request::detect_uri() : null;

        // Adding URL
        $model->addUrl($url);

        // Adding error source file and line number
        $model->addPath($file.':'.$line);

        if ($exception) {
            // Getting HTML stacktrace
            $e_response = \Kohana_Exception::response($exception);

            // Adding trace
            $model->setTrace((string)$e_response);
        }

        // Trying to get current module
        $module = $request ? $request->module() : null;

        // Adding module name for grouping purposes
        $model->addModule($module);

        // Saving
        $this->repository->save($model);

        $isNotificationNeeded = $this->isNotificationNeededFor($model, static::REPEAT_COUNT, static::REPEAT_DELAY);

        $this->debug('Notification needed is :value', [':value' => $isNotificationNeeded ? 'true' : 'false']);

        // Notify developers if needed
        if ($isNotificationNeeded) {
            $data = [
                'message'  => $model->getMessage(),
                'urls'     => $model->getUrls(),
                'paths'    => $model->getPaths(),
                'adminUrl' => $this->ifaceHelper->getReadEntityUrl($model, IFaceZone::ADMIN_ZONE),
            ];

            $message = $this->notificationHelper->createMessage('developer/error/php-exception');

            $this->notificationHelper->toDevelopers($message);

            $message
                ->setSubj('BetaKiller exception')
                ->setTemplateData($data)
                ->send();

            // Saving last notification timestamp
            $model->setLastNotifiedAt($currentTime);
            $this->repository->save($model);
        }

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
            $this->debug('Ignored exception');

            return false;
        }

        $lastSeenAt              = $model->getLastSeenAt();
        $lastSeenAtTimestamp     = $lastSeenAt->getTimestamp();
        $lastNotifiedAt          = $model->getLastNotifiedAt();
        $lastNotifiedAtTimestamp = $lastNotifiedAt ? $lastNotifiedAt->getTimestamp() : 0;

        $timeDiffInSeconds = $lastSeenAtTimestamp - $lastNotifiedAtTimestamp;

        $this->debug('Time diff between :last and :seen is :diff', [
            ':last' => $lastNotifiedAtTimestamp,
            ':seen' => $lastSeenAtTimestamp,
            ':diff' => $timeDiffInSeconds,
        ]);

        // Throttle by time
        if ($lastNotifiedAtTimestamp && ($timeDiffInSeconds < $repeatDelay)) {
            return false;
        }

        // New error needs to be notified only once
        if (!$lastNotifiedAtTimestamp && $model->isNew()) {
            $this->debug('New exception needs to be notified');

            return true;
        }

        // Repeated error needs to be notified
        if ($model->isRepeated()) {
            $this->debug('Repeated exception needs to be notified');

            return true;
        }

        $this->debug('Total counter is :value', [':value' => $model->getCounter()]);

        // Throttle by occurrence number
        return ($model->getCounter() % $repeatCount === 1);
    }
}
