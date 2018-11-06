<?php
namespace BetaKiller\Api\Method\PhpException;

use BetaKiller\Exception\HttpException;
use BetaKiller\Model\UserInterface;
use Spotman\Api\ApiMethodResponse;
use Spotman\Api\ArgumentsDefinitionInterface;
use Spotman\Api\ArgumentsInterface;

class ThrowHttpExceptionApiMethod extends AbstractPhpExceptionApiMethod
{
    private const ARG_CODE = 'code';

    /**
     * @return \Spotman\Api\ArgumentsDefinitionInterface
     */
    public function getArgumentsDefinition(): ArgumentsDefinitionInterface
    {
        return $this->definition()
            ->int(self::ARG_CODE);
    }

    /**
     * @param \Spotman\Api\ArgumentsInterface $arguments
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return \Spotman\Api\ApiMethodResponse|null
     * @throws \BetaKiller\Exception\HttpException
     */
    public function execute(ArgumentsInterface $arguments, UserInterface $user): ?ApiMethodResponse
    {
        $code = $arguments->getInt(self::ARG_CODE);

        throw new HttpException($code, 'This is a test from :username', [
            ':username' => $user->getUsername(),
        ]);
    }
}
