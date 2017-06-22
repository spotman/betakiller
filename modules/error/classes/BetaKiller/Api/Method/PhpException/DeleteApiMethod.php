<?php
namespace BetaKiller\Api\Method\PhpException;

class DeleteApiMethod extends AbstractPhpExceptionApiMethod
{
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
        $this->phpExceptionStorage->delete($this->model);

        return null;
    }
}
