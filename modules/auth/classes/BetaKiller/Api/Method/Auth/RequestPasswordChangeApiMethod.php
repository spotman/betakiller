<?php
declare(strict_types=1);

namespace BetaKiller\Api\Method\Auth;

use BetaKiller\Model\UserInterface;
use BetaKiller\Service\AuthService;
use Spotman\Api\ApiMethodResponse;
use Spotman\Api\Method\AbstractApiMethod;
use Spotman\Defence\ArgumentsInterface;
use Spotman\Defence\DefinitionBuilderInterface;

class RequestPasswordChangeApiMethod extends AbstractApiMethod
{
    /**
     * @var \BetaKiller\Service\AuthService
     */
    private $auth;

    /**
     * RequestPasswordChangeApiMethod constructor.
     *
     * @param \BetaKiller\Service\AuthService $auth
     */
    public function __construct(AuthService $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function getArgumentsDefinition(): DefinitionBuilderInterface
    {
        return $this->definition();
    }

    /**
     * @param \Spotman\Defence\ArgumentsInterface $arguments
     * @param \BetaKiller\Model\UserInterface     $user
     *
     * @return \Spotman\Api\ApiMethodResponse|null
     */
    public function execute(ArgumentsInterface $arguments, UserInterface $user): ?ApiMethodResponse
    {
        // No requests for other users here, only for caller
        $this->auth->requestPasswordChange($user);

        return null;
    }
}
