<?php
namespace BetaKiller\Api\Method\PhpException;

use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\PhpExceptionRepository;
use Spotman\Api\ApiMethodResponse;
use Spotman\Defence\ArgumentsDefinitionInterface;
use Spotman\Defence\ArgumentsInterface;

class IgnoreApiMethod extends AbstractPhpExceptionApiMethod
{
    private const ARG_HASH = 'hash';

    /**
     * @var PhpExceptionRepository
     */
    private $repository;

    /**
     * IgnoreApiMethod constructor.
     *
     * @param \BetaKiller\Repository\PhpExceptionRepository $repository
     */
    public function __construct(PhpExceptionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return \Spotman\Defence\ArgumentsDefinitionInterface
     */
    public function getArgumentsDefinition(): ArgumentsDefinitionInterface
    {
        return $this->definition()
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

        $model->markAsIgnoredBy($user);

        $this->repository->save($model);

        return null;
    }
}
