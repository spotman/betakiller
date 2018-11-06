<?php
namespace BetaKiller\Api\Method\PhpException;

use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\PhpExceptionRepository;
use Spotman\Api\ApiMethodResponse;
use Spotman\Api\ArgumentsDefinitionInterface;
use Spotman\Api\ArgumentsInterface;

class DeleteApiMethod extends AbstractPhpExceptionApiMethod
{
    private const ARG_HASH = 'hash';

    /**
     * @var \BetaKiller\Repository\PhpExceptionRepository
     */
    private $repository;

    /**
     * DeleteApiMethod constructor.
     *
     * @param PhpExceptionRepository $repository
     */
    public function __construct(PhpExceptionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return \Spotman\Api\ArgumentsDefinitionInterface
     */
    public function getArgumentsDefinition(): ArgumentsDefinitionInterface
    {
        return $this->definition()
            ->string(self::ARG_HASH);
    }

    /**
     * @param \Spotman\Api\ArgumentsInterface $arguments
     * @param \BetaKiller\Model\UserInterface $user
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
