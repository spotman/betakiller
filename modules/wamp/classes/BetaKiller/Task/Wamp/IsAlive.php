<?php
declare(strict_types=1);

namespace BetaKiller\Task\Wamp;

use BetaKiller\Api\Method\WampTest\DataApiMethod;
use BetaKiller\Daemon\AbstractApiWorkerDaemon;
use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\SessionHelper;
use BetaKiller\Model\UserInterface;
use BetaKiller\Service\MaintenanceModeService;
use BetaKiller\Session\SessionStorageInterface;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Wamp\WampClient;
use BetaKiller\Wamp\WampClientBuilder;
use Psr\Log\LoggerInterface;
use React\EventLoop\TimerInterface;
use Thruway\CallResult;
use Thruway\ClientSession;
use Thruway\Logging\Logger;

class IsAlive extends AbstractTask
{
    /**
     * @var \Mezzio\Session\SessionInterface|\Mezzio\Session\SessionIdentifierAwareInterface
     */
    private $session;

    /**
     * @var bool|null
     */
    private $isAlive;

    /**
     * @var \BetaKiller\Session\SessionStorageInterface
     */
    private $sessionStorage;

    /**
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * @var \BetaKiller\Wamp\WampClientBuilder
     */
    private $clientBuilder;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \BetaKiller\Env\AppEnvInterface
     */
    private $appEnv;

    /**
     * @var \BetaKiller\Service\MaintenanceModeService
     */
    private MaintenanceModeService $maintenance;

    /**
     * IsAlive constructor.
     *
     * @param \BetaKiller\Session\SessionStorageInterface $sessionStorage
     * @param \BetaKiller\Wamp\WampClientBuilder          $clientFactory
     * @param \BetaKiller\Env\AppEnvInterface             $appEnv
     * @param \BetaKiller\Service\MaintenanceModeService  $maintenance
     * @param \Psr\Log\LoggerInterface                    $logger
     */
    public function __construct(
        SessionStorageInterface $sessionStorage,
        WampClientBuilder       $clientFactory,
        AppEnvInterface         $appEnv,
        MaintenanceModeService  $maintenance,
        LoggerInterface         $logger
    ) {
        parent::__construct();

        $this->sessionStorage = $sessionStorage;
        $this->clientBuilder  = $clientFactory;
        $this->appEnv         = $appEnv;
        $this->logger         = $logger;
        $this->maintenance    = $maintenance;
    }

    /**
     * Put cli arguments with their default values here
     * Format: "optionName" => "defaultValue"
     *
     * @return array
     */
    public function defineOptions(): array
    {
        return [];
    }

    public function run(): void
    {
        // Skip checks during maintenance (API is down during maintenance)
        if ($this->maintenance->isEnabled()) {
            return;
        }

        $this->user = $this->getUser();

        $this->createSession();

        Logger::set($this->logger);

        // Use session auth for all connections
        $this->clientBuilder->sessionAuth($this->session);

        // Internal client for raw socket connection check
        $this->runTest($this->clientBuilder->internalConnection()->publicRealm()->create(), 'internal');

        if ($this->appEnv->inProductionMode()) {
            // External client for nginx proxy connection check
            $this->runTest($this->clientBuilder->externalConnection()->publicRealm()->create(), 'external');
        }

        $this->destroySession();
    }

    private function createSession(): void
    {
        $this->session = $this->sessionStorage->createSession();

        SessionHelper::setUserID($this->session, $this->user);

        $response = ResponseHelper::text('ok');

        $this->sessionStorage->persistSession($this->session, $response);
    }

    private function destroySession(): void
    {
        $this->sessionStorage->destroySession($this->session);
    }

    private function runTest(WampClient $client, string $connection): void
    {
        // Reset marker
        $this->isAlive = null;

        $client->on('open', function (ClientSession $session) use ($client) {
            $this->logger->debug('WAMP connection opened');

            $namedArgs = [
                AbstractApiWorkerDaemon::KEY_API_RESOURCE => 'WampTest',
                AbstractApiWorkerDaemon::KEY_API_METHOD   => 'data',
                AbstractApiWorkerDaemon::KEY_API_DATA     => [
                    DataApiMethod::ARG_CASE => DataApiMethod::CASE_STRING,
                ],
            ];

            $loop = $client->getLoop();

            $loop->addPeriodicTimer(1, function (TimerInterface $timer) use ($session, $loop, $client) {
                // Close session after check was done
                if (is_bool($this->isAlive)) {
                    $this->logger->debug('Check done, closing session');
                    $loop->cancelTimer($timer);

                    // Prevent reconnection after session close
                    $client->setAttemptRetry(false);

                    // Close session and exit
                    $session->close();
                } else {
                    $this->logger->debug('Waiting for check to be done...');
                }
            });

            $promise = $session->call(AbstractApiWorkerDaemon::PROCEDURE_API, [], $namedArgs);

            $promise->then(function (CallResult $result) {
                $args = (array)$result->getResultMessage()->getArguments()[0];

                $this->logger->debug('API result is :result', [
                    ':result' => json_encode($args),
                ]);

                if (!$args) {
                    $this->logger->warning('API call result is empty');
                    $this->isAlive = false;
                } elseif (isset($args['error'])) {
                    $this->logger->warning('API call result is ":error"', [
                        ':error' => $args['error'],
                    ]);
                    $this->isAlive = false;
                } else {
                    $this->logger->debug('API call succeeded');
                    $this->isAlive = true;
                }
            });

            $promise->otherwise(function () {
                $this->logger->debug('API call failed');
                $this->isAlive = false;
            });
        });

        // Start and wait for session.close event
        $client->start();

        if (!$this->isAlive) {
            $this->logger->emergency('WAMP router is not responding (:conn connection)', [
                ':conn' => $connection,
            ]);
        }
    }
}
