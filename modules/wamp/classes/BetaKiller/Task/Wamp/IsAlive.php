<?php
declare(strict_types=1);

namespace BetaKiller\Task\Wamp;

use BetaKiller\Api\Method\WampTest\DataApiMethod;
use BetaKiller\Config\WampConfigInterface;
use BetaKiller\Helper\CookieHelper;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\SessionHelper;
use BetaKiller\Model\UserInterface;
use BetaKiller\Session\DatabaseSessionStorage;
use BetaKiller\Session\SessionStorageInterface;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Wamp\WampInternalClient;
use Psr\Log\LoggerInterface;
use Thruway\Authentication\ClientWampCraAuthenticator;
use Thruway\CallResult;
use Thruway\ClientSession;
use Thruway\Connection;
use Thruway\Peer\Client;
use Thruway\Transport\PawlTransportProvider;

class IsAlive extends AbstractTask
{
    private const USER_AGENT = 'long-read-user-agent';

    /**
     * @var \BetaKiller\Config\WampConfigInterface
     */
    private $config;

    /**
     * @var Connection
     */
    private $conn;

    /**
     * @var \Zend\Expressive\Session\SessionInterface|\Zend\Expressive\Session\SessionIdentifierAwareInterface
     */
    private $session;

    /**
     * @var bool
     */
    private $isAlive = false;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \BetaKiller\Session\SessionStorageInterface
     */
    private $sessionStorage;

    /**
     * @var \BetaKiller\Helper\CookieHelper
     */
    private $cookieHelper;

    /**
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * IsAlive constructor.
     *
     * @param \BetaKiller\Config\WampConfigInterface      $config
     * @param \BetaKiller\Session\SessionStorageInterface $sessionStorage
     * @param \BetaKiller\Helper\CookieHelper             $cookieHelper
     * @param \Psr\Log\LoggerInterface                    $logger
     */
    public function __construct(
        WampConfigInterface $config,
        SessionStorageInterface $sessionStorage,
        CookieHelper $cookieHelper,
        UserInterface $user,
        LoggerInterface $logger
    ) {
        parent::__construct();

        $this->config         = $config;
        $this->logger         = $logger;
        $this->sessionStorage = $sessionStorage;
        $this->cookieHelper   = $cookieHelper;
        $this->user = $user;
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
        $url = sprintf(
            'ws://%s:%s',
            $this->config->getConnectionHost(),
            $this->config->getConnectionPort()
        );

        $this->createSession();

        // Encode SessionID like Cookies do
        $authId = $this->cookieHelper->encodeValue(
            DatabaseSessionStorage::COOKIE_NAME,
            $this->session->getId()
        );

        $client = new Client($this->config->getRealmName());
        $client->addTransportProvider(new PawlTransportProvider($url));
        $client->addClientAuthenticator(new ClientWampCraAuthenticator($authId, self::USER_AGENT));

        $client->setAuthId($authId);

        $client->setReconnectOptions([
            'max_retries'         => 0,
            'initial_retry_delay' => 1,
            'max_retry_delay'     => 3,
            'retry_delay_growth'  => 1.5,
        ]);

        $client->on('open', function (ClientSession $session) {
            $this->logger->debug('WAMP connection opened');

            $namedArgs = [
                WampInternalClient::KEY_API_RESOURCE => 'WampTest',
                WampInternalClient::KEY_API_METHOD   => 'data',
                WampInternalClient::KEY_API_DATA     => [
                    DataApiMethod::ARG_CASE => DataApiMethod::CASE_STRING,
                ],
            ];

            $promise = $session->call(WampInternalClient::PROCEDURE_API, [], $namedArgs);

            $promise->then(function (CallResult $result) {
                if ($result->getResultMessage()->getArguments()) {
                    $this->isAlive = true;
                }
            });

            $promise->always(function() use ($session) {
                $session->close();
            });
        });

        $client->start();

        $this->destroySession();

        if (!$this->isAlive) {
            $this->logger->emergency('WAMP router is not responding');
        }
    }

    private function createSession(): void
    {
        $this->session = $this->sessionStorage->createSession(
            self::USER_AGENT,
            '127.0.0.1',
            'https://google.com/'
        );

        SessionHelper::setUserID($this->session, $this->user);

        $response = ResponseHelper::text('ok');

        $this->sessionStorage->persistSession($this->session, $response);
    }

    private function destroySession(): void
    {
        $this->sessionStorage->destroySession($this->session);
    }
}