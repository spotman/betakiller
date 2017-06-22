<?php
namespace BetaKiller\Api\Method\PhpException;

use BetaKiller\Error\PhpExceptionStorageInterface;

class DeleteApiMethod extends AbstractPhpExceptionApiMethod
{
    /**
     * @var \BetaKiller\Error\PhpExceptionStorageInterface
     */
    private $storage;

    /**
     * @var \BetaKiller\Error\PhpExceptionModelInterface|null
     */
    private $model;

    /**
     * DeleteApiMethod constructor.
     *
     * @param string                                         $hash
     * @param \BetaKiller\Error\PhpExceptionStorageInterface $storage
     */
    public function __construct(string $hash, PhpExceptionStorageInterface $storage)
    {
        $this->storage = $storage;
        $this->model   = $this->findByHash($storage, $hash);
    }

    /**
     * @return \Spotman\Api\ApiMethodResponse|null
     */
    public function execute()
    {
        $this->storage->delete($this->model);

        return null;
    }
}
