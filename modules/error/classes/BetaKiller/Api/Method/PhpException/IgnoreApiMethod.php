<?php
namespace BetaKiller\Api\Method\PhpException;

use BetaKiller\Model\UserInterface;

class IgnoreApiMethod extends AbstractPhpExceptionApiMethod
{
    /**
     * @var \BetaKiller\Error\PhpExceptionModelInterface|null
     */
    protected $model;

    /**
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * IgnoreApiMethod constructor.
     *
     * @param string                          $hash
     * @param \BetaKiller\Model\UserInterface $user
     */
    public function __construct(string $hash, UserInterface $user)
    {
        $this->user  = $user;
        $this->model = $this->findByHash($hash);
    }

    /**
     * @return \Spotman\Api\ApiMethodResponse|null
     */
    public function execute()
    {
        $this->phpExceptionStorage->ignore($this->model, $this->user);

        return null;
    }
}
