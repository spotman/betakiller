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

        parent::__construct($wampConfig->getNamespace());
    }

    /**
     * @param \Thruway\ClientSession                $session
     * @param \Thruway\Transport\TransportInterface $transport
     */
    public function onSessionStart($session, $transport)
    {
        $this->registerApiValidationUserEmail();
    }

    /**
     * @return \BetaKiller\Wamp\WampClient
     */
    public function registerApiValidationUserEmail(): self
    {
        $apiFacade = $this->apiFacade;
        $procedure = function ($args) use ($apiFacade) {
            $validationApiResource = $apiFacade->get(
                'Validation',
                ApiResourceProxyInterface::INTERNAL
            );

            return $validationApiResource->call('UserEmail', ['email' => $args[0]]);
        };
        $this
            ->getSession()
            ->register('api.validation.userEmail', $procedure);

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
