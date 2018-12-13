<?php
declare(strict_types=1);

namespace BetaKiller\Wamp;

use BetaKiller\Api\ApiFacade;
use BetaKiller\Auth\AuthFacade;
use BetaKiller\Config\WampConfigInterface;
use BetaKiller\Exception;
use BetaKiller\Helper\CookieHelper;
use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Model\UserInterface;
use BetaKiller\Session\DatabaseSessionStorage;
use Psr\Log\LoggerInterface;
use Spotman\Api\ApiMethodResponse;
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
     * @var \BetaKiller\Helper\CookieHelper
     */
    private $cookieHelper;

    /**
     * @param \BetaKiller\Config\WampConfigInterface $wampConfig
     * @param \BetaKiller\Api\ApiFacade              $apiFacade
     * @param \BetaKiller\Auth\AuthFacade            $auth
     * @param \BetaKiller\Helper\CookieHelper        $cookieHelper
     * @param \Psr\Log\LoggerInterface               $logger
     */
    public function __construct(
        WampConfigInterface $wampConfig,
        ApiFacade $apiFacade,
        AuthFacade $auth,
        CookieHelper $cookieHelper,
        LoggerInterface $logger
    ) {
        parent::__construct($wampConfig->getRealmName());

        $this->apiFacade    = $apiFacade;
        $this->logger       = $logger;
        $this->auth         = $auth;
        $this->cookieHelper = $cookieHelper;
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

    public function apiCallProcedure(array $indexedArgs, \stdClass $namedArgs, \stdClass $options)
    {
        $sid = (string)$options->authid;

        if (!$sid) {
            $this->logException($this->logger, new Exception('Empty session id in wamp api call'));

            return null;
        }

        $sessionID = $this->cookieHelper->decodeValue(DatabaseSessionStorage::COOKIE_NAME, $sid);

        $user = $this->auth->getUserFromSessionID($sessionID);

        $this->logger->debug('Indexed args are :value', [':value' => \json_encode($indexedArgs)]);
        $this->logger->debug('Named args are :value', [':value' => \json_encode($namedArgs)]);

        $resource  = \ucfirst($namedArgs->resource);
        $method    = $namedArgs->method;
        $arguments = (array)$namedArgs->data;

        $this->logger->debug('User is :name', [':name' => $user->getUsername()]);
        $this->logger->debug('Resource is :name', [':name' => $resource]);
        $this->logger->debug('Method is :name', [':name' => $method]);
        $this->logger->debug('Arguments are :value', [':value' => \json_encode($arguments)]);

        $result = $this->callApiMethod($resource, $method, $arguments, $user);

        $this->logger->debug('Result is :value', [':value' => \json_encode($result)]);

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
}
