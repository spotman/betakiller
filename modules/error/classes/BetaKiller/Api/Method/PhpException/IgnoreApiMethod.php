<?php
namespace BetaKiller\Api\Method\PhpException;

use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\PhpExceptionRepository;
use Spotman\Api\ApiMethodResponse;

class IgnoreApiMethod extends AbstractPhpExceptionApiMethod
{
    /**
     * @var PhpExceptionRepository
     */
    private $repository;

    /**
     * @var \BetaKiller\Model\PhpExceptionModelInterface|null
     */
    private $model;

    /**
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * IgnoreApiMethod constructor.
     *
     * @param string                                        $hash
     * @param \BetaKiller\Repository\PhpExceptionRepository $repository
     * @param \BetaKiller\Model\UserInterface               $user
     */
    public function __construct(string $hash, PhpExceptionRepository $repository, UserInterface $user)
    {
        $this->repository = $repository;
        $this->user       = $user;
        $this->model      = $this->findByHash($repository, $hash);
    }

    /**
     * @return \Spotman\Api\ApiMethodResponse|null
     */
    public function execute(): ?ApiMethodResponse
    {
        $this->model->markAsIgnoredBy($this->user);

        $this->repository->save($this->model);

        return null;
    }
}
