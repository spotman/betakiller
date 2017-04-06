<?php
namespace BetaKiller\Api\Method\PhpException;

use Spotman\Api\Method\AbstractApiMethod;

class DeleteApiMethod extends AbstractApiMethod
{
    use PhpExceptionMethodTrait;

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
        $this->phpExceptionStorageFactory()->delete($this->model);

        return null;
    }
}
