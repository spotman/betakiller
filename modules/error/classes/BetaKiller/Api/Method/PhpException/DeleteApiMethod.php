<?php

namespace BetaKiller\Api\Method\PhpException;

use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\PhpExceptionRepository;
use Spotman\Api\ApiMethodResponse;
use Spotman\Defence\ArgumentsInterface;
use Spotman\Defence\DefinitionBuilderInterface;

readonly class DeleteApiMethod extends AbstractPhpExceptionApiMethod
{
    private const ARG_HASH = 'hash';

    /**
     * DeleteApiMethod constructor.
     *
     * @param PhpExceptionRepository $repository
     */
    public function __construct(private PhpExceptionRepository $repository)
    {
    }

    /**
     * @param \Spotman\Defence\DefinitionBuilderInterface $builder
     *
     * @return void
     */
    public function defineArguments(DefinitionBuilderInterface $builder): void
    {
        $builder
            ->string(self::ARG_HASH);
    }

    /**
     * @param \Spotman\Defence\ArgumentsInterface $arguments
     * @param \BetaKiller\Model\UserInterface     $user
     *
     * @return \Spotman\Api\ApiMethodResponse|null
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \Spotman\Api\ApiMethodException
     */
    public function execute(ArgumentsInterface $arguments, UserInterface $user): ?ApiMethodResponse
    {
        $hash  = $arguments->getString(self::ARG_HASH);
        $model = $this->findByHash($this->repository, $hash);

        $this->repository->delete($model);

        return null;
    }
}
