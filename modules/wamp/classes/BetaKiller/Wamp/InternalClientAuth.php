<?php
declare(strict_types=1);

namespace BetaKiller\Wamp;

use Thruway\Authentication\ClientAuthenticationInterface;
use Thruway\Message\AuthenticateMessage;
use Thruway\Message\ChallengeMessage;

class InternalClientAuth implements ClientAuthenticationInterface
{
    /**
     * @var mixed
     */
    private $authid;

    /**
     * Get AuthID
     *
     * @return mixed
     */
    public function getAuthId()
    {
        return $this->authid;
    }

    /**
     * Set AuthID
     *
     * @param mixed $authid
     */
    public function setAuthId($authid): void
    {
        $this->authid = $authid;
    }

    /**
     * Get list support authentication methods
     *
     * @return array
     */
    public function getAuthMethods(): array
    {
        return [InternalAuthProviderClient::METHOD_NAME];
    }

    /**
     * Make Authenticate message from challenge message
     *
     * @param \Thruway\Message\ChallengeMessage $msg
     *
     * @return \Thruway\Message\AuthenticateMessage
     */
    public function getAuthenticateFromChallenge(ChallengeMessage $msg): AuthenticateMessage
    {
        return new AuthenticateMessage(getenv('APP_REVISION'));
    }
}
