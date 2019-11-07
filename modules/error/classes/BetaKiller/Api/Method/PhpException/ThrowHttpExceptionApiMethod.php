<?php
namespace BetaKiller\Api\Method\PhpException;

use BetaKiller\Exception\HttpException;
use BetaKiller\Model\UserInterface;
use Spotman\Api\ApiMethodResponse;
use Spotman\Defence\ArgumentsInterface;
use Spotman\Defence\DefinitionBuilderInterface;

class ThrowHttpExceptionApiMethod extends AbstractPhpExceptionApiMethod
{
    private const ARG_CODE = 'code';

    /**
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function getArgumentsDefinition(): DefinitionBuilderInterface
    {
        return $this->definition()
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
