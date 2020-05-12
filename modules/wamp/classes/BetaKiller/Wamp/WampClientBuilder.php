<?php
declare(strict_types=1);

namespace BetaKiller\Wamp;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Config\WampConfigInterface;
use BetaKiller\DI\ContainerInterface;
use BetaKiller\Helper\CookieHelper;
use BetaKiller\Session\DatabaseSessionStorage;
use InvalidArgumentException;
use LogicException;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use Thruway\Authentication\ClientWampCraAuthenticator;
use Thruway\Transport\PawlTransportProvider;
use Zend\Expressive\Session\SessionIdentifierAwareInterface;
use Zend\Expressive\Session\SessionInterface;

class WampClientBuilder
{
    private const CONNECTION_INTERNAL = 'conn-int';
    private const CONNECTION_EXTERNAL = 'conn-ext';

    private const AUTH_INTERNAL = 'auth-int';
    private const AUTH_SESSION  = 'auth-ext';

    /**
     * @var \BetaKiller\Config\WampConfigInterface
     */
    private $wampConfig;

    /**
     * @var \BetaKiller\Config\AppConfigInterface
     */
    private $appConfig;

    /**
     * @var \BetaKiller\DI\ContainerInterface
     */
    private $container;

    /**
     * @var \BetaKiller\Helper\CookieHelper
     */
    private $cookieHelper;

    /**
     * @var string
     */
    private $connectionType;

    /**
     * @var string
     */
    private $authType;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var string
     */
    private $realm;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * WampClientBuilder constructor.
     *
     * @param \BetaKiller\Config\WampConfigInterface $wampConfig
     * @param \BetaKiller\Config\AppConfigInterface  $appConfig
     * @param \BetaKiller\DI\ContainerInterface      $container
     * @param \BetaKiller\Helper\CookieHelper        $cookieHelper
     * @param \Psr\Log\LoggerInterface               $logger
     */
    public function __construct(
        WampConfigInterface $wampConfig,
        AppConfigInterface $appConfig,
        ContainerInterface $container,
        CookieHelper $cookieHelper,
        LoggerInterface $logger
    ) {
        $this->wampConfig   = $wampConfig;
        $this->appConfig    = $appConfig;
        $this->container    = $container;
        $this->cookieHelper = $cookieHelper;
        $this->logger       = $logger;
    }

    public function internalConnection(): self
    {
        $this->connectionType = self::CONNECTION_INTERNAL;

        return $this;
    }

    public function externalConnection(): self
    {
        $this->connectionType = self::CONNECTION_EXTERNAL;

        return $this;
    }

    public function internalAuth(): self
    {
        $this->authType = self::AUTH_INTERNAL;

        return $this;
    }

    public function sessionAuth(SessionInterface $session): self
    {
        $this->authType = self::AUTH_SESSION;
        $this->session  = $session;

        return $this;
    }

    /**
     * @return $this
     * @deprecated
     */
    public function internalRealm(): WampClientBuilder
    {
        // Get internal realm
        $this->realm = $this->wampConfig->getInternalRealmName();

        return $this;
    }

    public function publicRealm(): WampClientBuilder
    {
        // Get public realm
        $this->realm = $this->wampConfig->getExternalRealmName();

        return $this;
    }

    public function create(LoopInterface $loop = null): WampClient
    {
        \Thruway\Logging\Logger::set($this->logger);

        /** @var \BetaKiller\Wamp\WampClient $client */
        $client = $this->container->make(WampClient::class, [
            'loop'  => $loop,
            'realm' => $this->realm,
        ]);

        $url = $this->makeUrl();

        $this->logger->debug('Connecting to wamp at :url', [
            ':url' => $url,
        ]);

        $client->addTransportProvider(new PawlTransportProvider($url));

        $this->useAuth($client);

        $client->setReconnectOptions([
            'max_retries'         => 100,
            'initial_retry_delay' => 1,
            'max_retry_delay'     => 100,
            'retry_delay_growth'  => 0,
        ]);

        return $client;
    }

    private function makeUrl(): string
    {
        switch ($this->connectionType) {
            case self::CONNECTION_INTERNAL:
                return sprintf(
                    'ws://%s:%s',
                    $this->wampConfig->getConnectionHost(),
                    $this->wampConfig->getConnectionPort()
                );

            case self::CONNECTION_EXTERNAL:
                return sprintf(
                    '%s://%s/wamp',
                    $this->appConfig->isSecure() ? 'wss' : 'ws',
                    $this->appConfig->getBaseUri()->getHost()
                );

            default:
                throw new InvalidArgumentException('Unknown connection type '.$this->connectionType);
        }
    }

    private function useAuth(WampClient $client): void
    {
        switch ($this->authType) {
            case self::AUTH_INTERNAL:
                $this->useInternalAuth($client);
                break;

            case self::AUTH_SESSION:
                $this->useSessionAuth($client);
                break;

            default:
                throw new InvalidArgumentException('Unknown auth type '.$this->authType);
        }
    }

    private function useInternalAuth(WampClient $client): void
    {
        $client->setAuthId(\md5(\microtime()));
        $client->addClientAuthenticator(new InternalClientAuth());
    }

    private function useSessionAuth(WampClient $client): void
    {
        if (!$this->session) {
            throw new LogicException('Missing session object');
        }

        if (!$this->session instanceof SessionIdentifierAwareInterface) {
            throw new LogicException('Session must implement '.SessionIdentifierAwareInterface::class);
        }

        // Encode SessionID like Cookies do
        $authId = $this->cookieHelper->encodeValue(
            DatabaseSessionStorage::COOKIE_NAME,
            $this->session->getId()
        );

        $auth = new ClientWampCraAuthenticator($authId, $authId); // No more user-agent here

        $client->addClientAuthenticator($auth);
        $client->setAuthId($authId);
    }
}
