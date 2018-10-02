<?php
declare(strict_types=1);

namespace BetaKiller\Wamp;

use BetaKiller\Api\ApiFacade;
use Spotman\Api\ApiResourceProxyInterface;
use BetaKiller\Config\WampConfig;

/**
 * https://github.com/voryx/Thruway#php-client-example
 * https://github.com/voryx/Thruway/blob/master/Examples/InternalClient/InternalClient.php
 */
class WampClient extends \Thruway\Peer\Client
{
    /**
     * @var \BetaKiller\Api\ApiFacade
     */
    private $apiFacade;

    /**
     * @param \BetaKiller\Config\WampConfig $wampConfig
     * @param \BetaKiller\Api\ApiFacade     $apiFacade
     */
    public function __construct(WampConfig $wampConfig, ApiFacade $apiFacade)
    {

        $this->apiFacade = $apiFacade;

        parent::__construct($wampConfig->getRealmName());
    }

    /**
     * @param \Thruway\ClientSession                $session
     * @param \Thruway\Transport\TransportInterface $transport
     */
    public function onSessionStart($session, $transport)
    {
        $this->registerApi();
    }

    /**
     * @return \BetaKiller\Wamp\WampClient
     */
    public function registerApi(): self
    {
        $apiFacade = $this->apiFacade;
        $procedure = function ($args) use ($apiFacade) {
            $validationApiResource = $apiFacade->get(
                'Validation',
                ApiResourceProxyInterface::INTERNAL
            );
            $response              = $validationApiResource->call('UserEmail', ['email' => $args[0]]);

            return $response->getData()['result'];
        };
        $this
            ->getSession()
            ->register('api.*.*', $procedure);

        return $this;
    }

    /**
     * @return array
     */
    public function getPhpVersion(): array
    {
        return [PHP_VERSION];
    }
}
