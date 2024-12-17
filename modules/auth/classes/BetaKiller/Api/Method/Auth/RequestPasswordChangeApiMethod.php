<?php

declare(strict_types=1);

namespace BetaKiller\Api\Method\Auth;

use BetaKiller\Model\UserInterface;
use BetaKiller\Service\AuthService;
use Spotman\Api\ApiMethodResponse;
use Spotman\Api\Method\AbstractApiMethod;
use Spotman\Defence\ArgumentsInterface;
use Spotman\Defence\DefinitionBuilderInterface;

readonly class RequestPasswordChangeApiMethod extends AbstractApiMethod
{
    /**
     * RequestPasswordChangeApiMethod constructor.
     *
     * @param \BetaKiller\Service\AuthService $auth
     */
    public function __construct(private AuthService $auth)
    {
    }

    /**
     * @param \Spotman\Defence\DefinitionBuilderInterface $builder
     *
     * @return void
     */
    public function defineArguments(DefinitionBuilderInterface $builder): void
    {
        // No arguments
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
