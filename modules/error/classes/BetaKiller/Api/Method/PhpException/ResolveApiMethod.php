<?php
namespace BetaKiller\Api\Method\PhpException;

use BetaKiller\Error\PhpExceptionStorageInterface;
use BetaKiller\Model\UserInterface;

class ResolveApiMethod extends AbstractPhpExceptionApiMethod
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
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * ResolveApiMethod constructor.
     *
     * @param string                                         $hash
     * @param \BetaKiller\Error\PhpExceptionStorageInterface $storage
     * @param \BetaKiller\Model\UserInterface                $user
     */
    public function __construct(string $hash, PhpExceptionStorageInterface $storage, UserInterface $user)
    {
        $this->storage = $storage;
        $this->user  = $user;
        $this->model = $this->findByHash($storage, $hash);
    }

    /**
     * @return \Spotman\Api\ApiMethodResponse|null
     */
    public function execute()
    {
        $this->storage->resolve($this->model, $this->user);

        return null;
    }
}
