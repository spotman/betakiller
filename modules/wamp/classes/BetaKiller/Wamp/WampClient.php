<?php
declare(strict_types=1);

namespace BetaKiller\Wamp;

use BetaKiller\Api\ApiFacade;
use BetaKiller\Auth\AuthFacade;
use BetaKiller\Config\WampConfig;
use BetaKiller\Exception;
use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Model\UserInterface;
use Psr\Log\LoggerInterface;
use Spotman\Api\ApiResourceProxyInterface;

/**
 * https://github.com/voryx/Thruway#php-client-example
 * https://github.com/voryx/Thruway/blob/master/Examples/InternalClient/InternalClient.php
 */
class WampClient extends \Thruway\Peer\Client
{
    use LoggerHelperTrait;

    /**
     * @var \BetaKiller\Api\ApiFacade
     */
    private $apiFacade;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \BetaKiller\Auth\AuthFacade
     */
    private $auth;

    /**
     * @param \BetaKiller\Config\WampConfig $wampConfig
     * @param \BetaKiller\Api\ApiFacade     $apiFacade
     * @param \BetaKiller\Auth\AuthFacade   $auth
     * @param \Psr\Log\LoggerInterface      $logger
     *
     * @throws \BetaKiller\Exception
     */
    public function __construct(WampConfig $wampConfig, ApiFacade $apiFacade, AuthFacade $auth, LoggerInterface $logger)
    {
        parent::__construct($wampConfig->getRealmName());

        $this->apiFacade = $apiFacade;
        $this->logger    = $logger;
        $this->auth      = $auth;
    }

    /**
     * @param \Thruway\ClientSession                $session
     * @param \Thruway\Transport\TransportInterface $transport
     */
    public function onSessionStart($session, $transport)
    {
        $session->register('api', [$this, 'apiCallProcedure'], [
//            'caller_identification' => true,
            'disclose_caller' => true,
        ]);
    }

    public function apiCallProcedure(array $arguments, \stdClass $dummy, \stdClass $options)
    {
        $sessionID = (string)$options->authid;

        if (!$sessionID) {
            $this->logException($this->logger, new Exception('Empty session id in wamp api call'));

            return null;
        }

        $user = $this->auth->getUserFromSessionID($sessionID);

        $resource = \array_shift($arguments);
        $method   = \array_shift($arguments);

        $resource = \ucfirst($resource);

        $this->logger->debug('User is :name', [':name' => $user->getUsername()]);
        $this->logger->debug('Resource is :name', [':name' => $resource]);
        $this->logger->debug('Method is :name', [':name' => $method]);
        $this->logger->debug('Arguments are :value', [':value' => \json_encode($arguments)]);

        $result = $this->callApiMethod($resource, $method, $arguments, $user);

        $this->logger->debug('Result is :value', [':value' => \json_encode($result)]);

        return $result;
    }

    private function callApiMethod(string $resource, string $method, array $arguments, UserInterface $user)
    {
        return $this->apiFacade
            ->getResource($resource, ApiResourceProxyInterface::INTERNAL)
            ->call($method, $arguments, $user)
            ->getData();
    }
}
