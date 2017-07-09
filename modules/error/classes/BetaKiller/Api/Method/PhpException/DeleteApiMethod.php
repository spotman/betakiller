<?php
namespace BetaKiller\Api\Method\PhpException;

use BetaKiller\Repository\PhpExceptionRepository;
use Spotman\Api\ApiMethodResponse;

class DeleteApiMethod extends AbstractPhpExceptionApiMethod
{
    /**
     * @var \BetaKiller\Repository\PhpExceptionRepository
     */
    private $repository;

    /**
     * @var \BetaKiller\Model\PhpExceptionModelInterface|null
     */
    private $model;

    /**
     * DeleteApiMethod constructor.
     *
     * @param string                 $hash
     * @param PhpExceptionRepository $repository
     */
    public function __construct(string $hash, PhpExceptionRepository $repository)
    {
        $this->repository = $repository;
        $this->model      = $this->findByHash($repository, $hash);
    }

    /**
     * @return \Spotman\Api\ApiMethodResponse|null
     */
    public function execute(): ?ApiMethodResponse
    {
        $this->repository->delete($this->model);

        return null;
    }
}
