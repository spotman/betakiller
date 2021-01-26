<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Api\ApiFacade;
use BetaKiller\Error\ExceptionService;
use BetaKiller\Helper\LoggerHelper;
use BetaKiller\Model\RoleInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Service\MaintenanceModeService;
use BetaKiller\Wamp\WampClient;
use BetaKiller\Wamp\WampClientBuilder;
use BetaKiller\Wamp\WampClientHelper;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use Spotman\Api\ApiMethodResponse;
use Spotman\Api\ApiResourceProxyInterface;
use stdClass;
use Symfony\Component\Stopwatch\Stopwatch;
use Throwable;
use Thruway\ClientSession;
use Thruway\Logging\Logger;
use Thruway\Registration;

abstract class AbstractApiWorkerDaemon extends AbstractDaemon
{
    public const CODENAME = 'ApiWorker';

    public const PROCEDURE_API = 'api';

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
     * @param \BetaKiller\Wamp\WampClientBuilder         $clientFactory
     * @param \BetaKiller\Api\ApiFacade                  $apiFacade
     * @param \BetaKiller\Error\ExceptionService         $exceptionService
     * @param \BetaKiller\Wamp\WampClientHelper          $clientHelper
     * @param \BetaKiller\Service\MaintenanceModeService $maintenance
     * @param \Psr\Log\LoggerInterface                   $logger
     */
    public function __construct(
        WampClientBuilder $clientFactory,
        ApiFacade $apiFacade,
        ExceptionService $exceptionService,
        WampClientHelper $clientHelper,
        MaintenanceModeService $maintenance,
        LoggerInterface $logger
    ) {
        $this->clientBuilder    = $clientFactory;
        $this->apiFacade        = $apiFacade;
        $this->exceptionService = $exceptionService;
        $this->clientHelper     = $clientHelper;
        $this->maintenance      = $maintenance;
        $this->logger           = $logger;
    }

    public function startDaemon(LoopInterface $loop): void
    {
        Logger::set($this->logger);

        // Restart every hour coz of annoying memory leak
        $loop->addTimer(60 * 60, function () use ($loop) {
            $this->logger->info('Stopping API worker to prevent memory leaks');
            $this->stopDaemon($loop);
            $loop->stop();
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
    }

    public function stopDaemon(LoopInterface $loop): void
    {
        // Stop client and disconnect
        $this->wampClient->onClose('Stopped');
    }

    private function apiCallProcedure(array $indexedArgs, stdClass $namedArgs): array
    {
        $user = null;

        try {
            $t = (new Stopwatch(true))->start('api');

            $wampSession = $this->clientHelper->getProcedureSession(func_get_args());
            $user        = $this->clientHelper->getSessionUser($wampSession);

//            $this->logger->debug('Indexed args are :value', [':value' => json_encode($indexedArgs)]);
//            $this->logger->debug('Named args are :value', [':value' => json_encode($namedArgs)]);

            // Prevent calling API methods during maintenance for non-developers
            if (!$user->hasRoleName(RoleInterface::DEVELOPER) && $this->maintenance->isEnabled()) {
                return [
                    'error' => 'maintenance',
                ];
            }

            $arrayArgs = (array)$namedArgs;

            $resource  = ucfirst($arrayArgs[self::KEY_API_RESOURCE]);
            $method    = $arrayArgs[self::KEY_API_METHOD];
            $arguments = (array)$arrayArgs[self::KEY_API_DATA];

            $this->logger->debug('User is ":name"', [':name' => $user->getID()]);
            $this->logger->debug('Resource/method are :resource.:method', [
                ':resource' => $resource,
                ':method'   => $method,
            ]);
            $this->logger->debug('Arguments are :value', [':value' => json_encode($arguments)]);

            $result = $this->callApiMethod($resource, $method, $arguments, $user)->jsonSerialize();

            $this->logger->debug(':resource.:method executed in :time ms', [
                ':resource' => $resource,
                ':method'   => $method,
                ':time'     => $t->stop()->getDuration(),
            ]);
        } catch (Throwable $e) {
            return $this->makeApiError($e, $user);
        }

//        $this->logger->debug('Result is :value', [':value' => json_encode($result)]);

        return $result;
    }

    private function callApiMethod(
        string $resource,
        string $method,
        array $arguments,
        UserInterface $user
    ): ApiMethodResponse {
        return $this->apiFacade
            ->getResource($resource, ApiResourceProxyInterface::INTERNAL)
            ->call($method, $arguments, $user);
    }

    private function makeApiError(Throwable $e, UserInterface $user = null): array
    {
        if ($user) {
            LoggerHelper::logUserException($this->logger, $e, $user);
        } else {
            LoggerHelper::logRawException($this->logger, $e);
        }

        $lang  = $user ? $user->getLanguage() : null;
        $error = $this->exceptionService->getExceptionMessage($e, $lang);

        $this->logger->debug('Error is ":value"', [':value' => $error]);

        return [
            'error' => $error,
        ];
    }
}
