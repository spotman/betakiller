<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Api\ApiFacade;
use BetaKiller\Error\ExceptionService;
use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Model\UserInterface;
use BetaKiller\Wamp\WampClient;
use BetaKiller\Wamp\WampClientBuilder;
use BetaKiller\Wamp\WampClientHelper;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use Spotman\Api\ApiMethodResponse;
use Spotman\Api\ApiResourceProxyInterface;
use stdClass;
use Throwable;
use Thruway\ClientSession;

class ApiWorkerDaemon implements DaemonInterface
{
    public const CODENAME = 'ApiWorker';

    public const PROCEDURE_API = 'api';

    public const KEY_API_RESOURCE = 'resource';
    public const KEY_API_METHOD   = 'method';
    public const KEY_API_DATA     = 'data';

    use LoggerHelperTrait;

    /**
     * @var \BetaKiller\Wamp\WampClientBuilder
     */
    private $clientBuilder;

    /**
     * @var WampClient[]
     */
    private $wampClients;

    /**
     * @var \BetaKiller\Wamp\WampClientHelper
     */
    private $clientHelper;

    /**
     * @var \BetaKiller\Api\ApiFacade
     */
    private $apiFacade;

    /**
     * @var \BetaKiller\Error\ExceptionService
     */
    private $exceptionService;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \BetaKiller\Wamp\WampClientBuilder $clientFactory
     * @param \BetaKiller\Api\ApiFacade          $apiFacade
     * @param \BetaKiller\Error\ExceptionService $exceptionService
     * @param \BetaKiller\Wamp\WampClientHelper  $clientHelper
     * @param \Psr\Log\LoggerInterface           $logger
     */
    public function __construct(
        WampClientBuilder $clientFactory,
        ApiFacade $apiFacade,
        ExceptionService $exceptionService,
        WampClientHelper $clientHelper,
        LoggerInterface $logger
    ) {
        $this->clientBuilder    = $clientFactory;
        $this->apiFacade        = $apiFacade;
        $this->exceptionService = $exceptionService;
        $this->clientHelper     = $clientHelper;
        $this->logger           = $logger;
    }

    public function start(LoopInterface $loop): void
    {
        \Thruway\Logging\Logger::set($this->logger);

        // Restart every 24h coz of annoying memory leak
        $loop->addTimer(60 * 1440, function () use ($loop) {
            $this->logger->info('Stopping API worker to prevent memory leaks');
            $this->stop();
            $loop->stop();
        });

        // Use internal auth and connection coz it is an internal worker
        $this->clientBuilder->internalAuth()->internalConnection();

        $this->wampClients = [
            // For processing external API requests in 'external' realm (public clients)
            $this->clientBuilder->createExternal($loop),

            // For processing internal API requests in 'internal' realm (workers, checkers, etc)
            $this->clientBuilder->createInternal($loop),
        ];

        $this->clientHelper->bindSessionHandlers($loop);

        // Bind events and start every client
        foreach ($this->wampClients as $wampClient) {
            $wampClient->on('open', function (ClientSession $session) {
                // Register API handler
                $session->register('api', [$this, 'apiCallProcedure'], [
                    'disclose_caller' => true,
                ]);
            });

            $wampClient->start(false);
        }
    }

    public function stop(): void
    {
        foreach ($this->wampClients as $wampClient) {
            $wampClient->onClose('Stopped');
        }
    }

    private function apiCallProcedure(array $indexedArgs, stdClass $namedArgs)
    {
        $user = null;

        try {
            $wampSession = $this->clientHelper->getProcedureSession(func_get_args());
            $user        = $this->clientHelper->getSessionUser($wampSession);

            $this->logger->debug('Indexed args are :value', [':value' => json_encode($indexedArgs)]);
            $this->logger->debug('Named args are :value', [':value' => json_encode($namedArgs)]);

            $arrayArgs = (array)$namedArgs;

            $resource  = ucfirst($arrayArgs[self::KEY_API_RESOURCE]);
            $method    = $arrayArgs[self::KEY_API_METHOD];
            $arguments = (array)$arrayArgs[self::KEY_API_DATA];

            $this->logger->debug('User is ":name"', [':name' => $user->getUsername()]);
            $this->logger->debug('Resource is ":name"', [':name' => $resource]);
            $this->logger->debug('Method is ":name"', [':name' => $method]);
            $this->logger->debug('Arguments are :value', [':value' => json_encode($arguments)]);

            $result = $this->callApiMethod($resource, $method, $arguments, $user);
        } catch (Throwable $e) {
            return $this->makeApiError($e, $user);
        }

        $this->logger->debug('Result is :value', [':value' => json_encode($result)]);

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
        $this->logException($this->logger, $e);

        $lang  = $user ? $user->getLanguage() : null;
        $error = $this->exceptionService->getExceptionMessage($e, $lang);

        $this->logger->debug('Error is ":value"', [':value' => $error]);

        return [
            'error' => $error,
        ];
    }
}
