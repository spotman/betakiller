<?php
namespace BetaKiller\Error;

use BetaKiller\Exception;
use BetaKiller\Factory\OrmFactory;
use BetaKiller\Helper\LogTrait;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Model\IFaceZone;
use BetaKiller\Model\UserInterface;

class PhpExceptionStorage implements PhpExceptionStorageInterface
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
     * @var \BetaKiller\Factory\OrmFactory
     */
    private $ormFactory;

    /**
     * @var \BetaKiller\Helper\NotificationHelper
     */
    private $notificationHelper;

    /**
     * @var UserInterface;
     */
    private $user;

    /**
     * @Inject
     * TODO move to constructor
     * @var \BetaKiller\Helper\IFaceHelper
     */
    private $ifaceHelper;

    /**
     * PhpExceptionStorage constructor.
     *
     * @param \BetaKiller\Factory\OrmFactory        $ormFactory
     * @param \BetaKiller\Helper\NotificationHelper $notificationHelper
     * @param \BetaKiller\Model\UserInterface       $user
     */
    public function __construct(OrmFactory $ormFactory, NotificationHelper $notificationHelper, UserInterface $user)
    {
        $this->user               = $user;
        $this->ormFactory         = $ormFactory;
        $this->notificationHelper = $notificationHelper;
    }

    /**
     * @param \BetaKiller\Error\PhpExceptionModelInterface $model
     *
     * @return string
     */
    public function getTraceFor(PhpExceptionModelInterface $model)
    {
        $path = $this->getTraceFullPathFor($model);

        return file_get_contents($path);
    }

    /**
     * @param \BetaKiller\Error\PhpExceptionModelInterface $model
     * @param string                                       $traceResponse
     *
     * @return $this
     */
    public function setTraceFor(PhpExceptionModelInterface $model, $traceResponse)
    {
        $path = $this->getTraceFullPathFor($model);
        file_put_contents($path, (string)$traceResponse);

        return $this;
    }

    protected function getTraceFullPathFor(PhpExceptionModelInterface $model)
    {
        $dir = MODPATH.'error/media/php_traces';

        if (!file_exists($dir) && !@mkdir($dir, 0664, true) && !is_dir($dir)) {
            throw new Exception('Can not create directory [:dir] for php stacktrace files', [':dir' => $dir]);
        }

        return $dir.DIRECTORY_SEPARATOR.$model->getHash();
    }

    /**
     * @param \Throwable $exception
     *
     * @return PhpExceptionModelInterface|null
     */
    public function storeException(\Throwable $exception)
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

        $orm = $this->phpExceptionModelFactory();

        // Searching for existing exception
        $model = $orm->findByHash($hash);

        $currentTime = new \DateTime;

        if ($model) {
            // Mark exception as repeated
            $model
                ->setLastSeenAt($currentTime)
                ->markAsRepeated($user);
        } else {
            $model = $orm
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
            $this->setTraceFor($model, (string)$e_response);
        }

        // Trying to get current module
        $module = $request ? $request->module() : null;

        // Adding module name for grouping purposes
        $model->addModule($module);

        // Saving
        $model->save();

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
            $model->setLastNotifiedAt($currentTime)->save();
        }

        return $model;
    }

    public function delete(PhpExceptionModelInterface $model)
    {
        @unlink($this->getTraceFullPathFor($model));

        $model->delete();
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
    public function isNotificationNeededFor(PhpExceptionModelInterface $model, $repeatCount, $repeatDelay)
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

    /**
     * @return PhpExceptionModelInterface[]
     */
    public function getUnresolvedPhpExceptions()
    {
        return $this->phpExceptionModelFactory()->filterUnresolved()->orderByCreatedAt()->get_all();
    }

    /**
     * @return PhpExceptionModelInterface[]
     */
    public function getResolvedPhpExceptions()
    {
        return $this->phpExceptionModelFactory()->filterResolved()->orderByCreatedAt()->get_all();
    }

    /**
     * @param string $hash
     *
     * @return \BetaKiller\Error\PhpExceptionModelInterface|null
     */
    public function findByHash($hash)
    {
        return $this->phpExceptionModelFactory()->findByHash($hash);
    }

    /**
     * @param \BetaKiller\Error\PhpExceptionModelInterface $model
     * @param \BetaKiller\Model\UserInterface              $user
     */
    public function resolve(PhpExceptionModelInterface $model, UserInterface $user)
    {
        $model->markAsResolvedBy($user)->save();
    }

    /**
     * @param \BetaKiller\Error\PhpExceptionModelInterface $model
     * @param \BetaKiller\Model\UserInterface              $user
     */
    public function ignore(PhpExceptionModelInterface $model, UserInterface $user)
    {
        $model->markAsIgnoredBy($user)->save();
    }

    /**
     * @return \BetaKiller\Utils\Kohana\ORM\OrmInterface|\Model_PhpException
     */
    private function phpExceptionModelFactory()
    {
        return $this->ormFactory->create('PhpException');
    }
}
