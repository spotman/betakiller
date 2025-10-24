<?php

declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Api\ApiFacade;
use BetaKiller\Error\ExceptionService;
use BetaKiller\Helper\LoggerHelper;
use BetaKiller\Model\UserInterface;
use BetaKiller\Monitoring\MetricsCollectorInterface;
use BetaKiller\Service\MaintenanceModeService;
use BetaKiller\Wamp\WampClient;
use BetaKiller\Wamp\WampClientBuilder;
use BetaKiller\Wamp\WampClientHelper;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use stdClass;
use Throwable;
use Thruway\ClientSession;
use Thruway\Logging\Logger;
use Thruway\Registration;

use function React\Promise\resolve;

abstract class AbstractApiWorkerDaemon extends AbstractDaemon
{
    public const CODENAME = 'ApiWorker';

    public const PROCEDURE_API = 'api';

    public const KEY_API_SOURCE   = 'source';
    public const KEY_API_RESOURCE = 'resource';
    public const KEY_API_METHOD   = 'method';
    public const KEY_API_DATA     = 'data';

    /**
     * @var \BetaKiller\Wamp\WampClientBuilder
     */
    private WampClientBuilder $clientBuilder;

    /**
     * @var WampClient
     */
    private WampClient $wampClient;

    /**
     * @var \BetaKiller\Wamp\WampClientHelper
     */
    private WampClientHelper $clientHelper;

    /**
     * @var \BetaKiller\Api\ApiFacade
     */
    private ApiFacade $apiFacade;

    /**
     * @var \BetaKiller\Error\ExceptionService
     */
    private ExceptionService $exceptionService;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var \BetaKiller\Service\MaintenanceModeService
     */
    private MaintenanceModeService $maintenance;

    /**
     * @var \BetaKiller\Monitoring\MetricsCollectorInterface
     */
    private MetricsCollectorInterface $metrics;

    /**
     * @param \BetaKiller\Wamp\WampClientBuilder               $clientFactory
     * @param \BetaKiller\Api\ApiFacade                        $apiFacade
     * @param \BetaKiller\Error\ExceptionService               $exceptionService
     * @param \BetaKiller\Wamp\WampClientHelper                $clientHelper
     * @param \BetaKiller\Service\MaintenanceModeService       $maintenance
     * @param \BetaKiller\Monitoring\MetricsCollectorInterface $metrics
     * @param \Psr\Log\LoggerInterface                         $logger
     */
    public function __construct(
        WampClientBuilder $clientFactory,
        ApiFacade $apiFacade,
        ExceptionService $exceptionService,
        WampClientHelper $clientHelper,
        MaintenanceModeService $maintenance,
        MetricsCollectorInterface $metrics,
        LoggerInterface $logger
    ) {
        $this->clientBuilder    = $clientFactory;
        $this->apiFacade        = $apiFacade;
        $this->exceptionService = $exceptionService;
        $this->clientHelper     = $clientHelper;
        $this->maintenance      = $maintenance;
        $this->logger           = $logger;
        $this->metrics          = $metrics;
    }

    public function startDaemon(LoopInterface $loop): PromiseInterface
    {
        Logger::set($this->logger);

        // Restart every hour coz of annoying memory leak
        $loop->addTimer(60 * 60, function () use ($loop) {
            $this->logger->info('Stopping API worker to prevent memory leaks');
            $this->stopDaemon($loop);
            $loop->stop();
        });

        $loop->addPeriodicTimer(5, function () {
            $this->metrics->flush();
        });

        // Use internal auth and connection coz it is an internal worker
        $this->wampClient = $this->clientBuilder
            ->internalConnection()
            ->internalAuth()
            ->publicRealm()
            ->create($loop);

        $this->wampClient->onSessionOpen(function (ClientSession $session) {
            // Register API handler
            $session->register(
                'api',
                function () {
                    $this->markAsProcessing();

                    $result = $this->apiCallProcedure(...\func_get_args());

                    $this->markAsIdle();

                    return $result;
                },
                [
                    'disclose_caller' => true,
                    'invoke'          => Registration::ROUNDROBIN_REGISTRATION,
                ]
            );
        });

        $this->wampClient->bindPingHandlers();

        $this->wampClient->start(false);

        return resolve();
    }

    public function stopDaemon(LoopInterface $loop): PromiseInterface
    {
        // Stop client and disconnect
        $this->wampClient->setAttemptRetry(false);
        $this->wampClient->onClose('Stopped');

        return resolve();
    }

    private function apiCallProcedure(array $indexedArgs, stdClass $namedArgs): array
    {
        $user = null;

        try {
            $maintenanceBegin = \microtime(true);

            // Prevent calling API methods during maintenance
            if ($this->maintenance->isEnabled()) {
                return [
                    'error' => 'maintenance',
                ];
            }

            $userBegin = \microtime(true);

            $wampSession = $this->clientHelper->getProcedureSession(func_get_args());
            $user        = $this->clientHelper->getSessionUser($wampSession);

            $userTime        = (microtime(true) - $userBegin) * 1000;
            $maintenanceTime = ($userBegin - $maintenanceBegin) * 1000;

//            $this->logger->debug('Indexed args are :value', [':value' => json_encode($indexedArgs)]);
//            $this->logger->debug('Named args are :value', [':value' => json_encode($namedArgs)]);

            $arrayArgs = (array)$namedArgs;

            $resource  = ucfirst($arrayArgs[self::KEY_API_RESOURCE]);
            $method    = $arrayArgs[self::KEY_API_METHOD];
            $arguments = (array)$arrayArgs[self::KEY_API_DATA];
            $source    = $arrayArgs[self::KEY_API_SOURCE] ?? null;

            $this->logger->debug('User is ":name"', [
                ':name' => $user->getID(),
            ]);

            $this->logger->debug('Source is ":source"', [
                ':source' => $source,
            ]);

            $this->logger->debug('Resource/method are :resource.:method', [
                ':resource' => $resource,
                ':method'   => $method,
            ]);

            $this->logger->debug('Arguments are :value', [
                ':value' => json_encode($arguments),
            ]);

            $this->metrics->timing('api.worker.prepare.user', $userTime);
            $this->metrics->timing('api.worker.prepare.maintenance', $maintenanceTime);

            return $this->apiFacade->call($resource, $method, $arguments, $user)->jsonSerialize();
        } catch (Throwable $e) {
            return $this->makeApiError($e, $user);
        }
    }

    private function makeApiError(Throwable $e, UserInterface $user = null): array
    {
        if ($user) {
            LoggerHelper::logUserException($this->logger, $e, $user);
        } else {
            LoggerHelper::logRawException($this->logger, $e);
        }

        $lang  = $user?->getLanguage();
        $error = $this->exceptionService->getExceptionMessage($e, $lang);

        $this->logger->debug('Error is ":value"', [':value' => $error]);

        return [
            'error' => $error,
        ];
    }
}
