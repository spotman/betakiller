<?php

namespace BetaKiller\Api\Method\PhpException;

use BetaKiller\Exception\HttpException;
use BetaKiller\Model\UserInterface;
use Spotman\Api\ApiMethodResponse;
use Spotman\Defence\ArgumentsInterface;
use Spotman\Defence\DefinitionBuilderInterface;

readonly class ThrowHttpExceptionApiMethod extends AbstractPhpExceptionApiMethod
{
    private const ARG_CODE = 'code';

    /**
     * @param \Spotman\Defence\DefinitionBuilderInterface $builder
     *
     * @return void
     */
    public function defineArguments(DefinitionBuilderInterface $builder): void
    {
        $builder
            ->int(self::ARG_CODE);
    }

    /**
     * @param \Spotman\Defence\ArgumentsInterface $arguments
     * @param \BetaKiller\Model\UserInterface     $user
     *
     * @return \Spotman\Api\ApiMethodResponse|null
     * @throws \BetaKiller\Exception\HttpException
     */
    public function execute(ArgumentsInterface $arguments, UserInterface $user): ?ApiMethodResponse
    {
        $code = $arguments->getInt(self::ARG_CODE);

        throw new HttpException($code, 'This is a test from :user', [
            ':user' => $user->getEmail(),
        ]);
    }
}
