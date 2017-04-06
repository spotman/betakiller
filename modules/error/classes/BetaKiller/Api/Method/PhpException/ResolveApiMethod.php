<?php
namespace BetaKiller\Api\Method\PhpException;

use BetaKiller\Helper\CurrentUserTrait;
use Spotman\Api\Method\AbstractApiMethod;

class ResolveApiMethod extends AbstractApiMethod
{
    use PhpExceptionMethodTrait;
    use CurrentUserTrait;

    /**
     * @var \BetaKiller\Error\PhpExceptionModelInterface|null
     */
    protected $model;

    /**
     * ResolveApiMethod constructor.
     *
     * @param string $hash
     */
    public function __construct($hash)
    {
        $this->model = $this->findByHash($hash);
    }

    /**
     * @return \Spotman\Api\ApiMethodResponse|null
     */
    public function execute()
    {
        $this->phpExceptionStorageFactory()->resolve($this->model, $this->current_user());

        return null;
    }
}
