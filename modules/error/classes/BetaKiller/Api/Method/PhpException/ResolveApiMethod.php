<?php
namespace BetaKiller\Api\Method\PhpException;

use BetaKiller\Model\UserInterface;
use Spotman\Api\Method\AbstractApiMethod;

class ResolveApiMethod extends AbstractApiMethod
{
    use PhpExceptionMethodTrait;

    /**
     * @var \BetaKiller\Error\PhpExceptionModelInterface|null
     */
    protected $model;

    /**
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * ResolveApiMethod constructor.
     *
     * @param string                          $hash
     * @param \BetaKiller\Model\UserInterface $user
     */
    public function __construct($hash, UserInterface $user)
    {
        $this->user  = $user;
        $this->model = $this->findByHash($hash);
    }

    /**
     * @return \Spotman\Api\ApiMethodResponse|null
     */
    public function execute()
    {
        $this->phpExceptionStorage->resolve($this->model, $this->user);

        return null;
    }
}
