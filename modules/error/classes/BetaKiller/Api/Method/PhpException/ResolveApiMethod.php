<?php

namespace BetaKiller\Api\Method\PhpException;

use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\PhpExceptionRepository;
use Spotman\Api\ApiMethodResponse;
use Spotman\Defence\ArgumentsInterface;
use Spotman\Defence\DefinitionBuilderInterface;

readonly class ResolveApiMethod extends AbstractPhpExceptionApiMethod
{
    private const ARG_HASH = 'hash';

    /**
     * ResolveApiMethod constructor.
     *
     * @param \BetaKiller\Repository\PhpExceptionRepository $repository
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
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \Spotman\Api\ApiMethodException
     */
    public function execute(ArgumentsInterface $arguments, UserInterface $user): ?ApiMethodResponse
    {
        $hash  = $arguments->getString(self::ARG_HASH);
        $model = $this->findByHash($this->repository, $hash);

        $model->markAsResolvedBy($user);

        $this->repository->save($model);

        return null;
    }
}
