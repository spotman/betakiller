<?php
namespace BetaKiller\Api\Method\PhpException;

use Spotman\Api\ApiMethodException;

trait PhpExceptionMethodTrait
{
    /**
     * @Inject
     * @var \BetaKiller\Error\PhpExceptionStorageInterface
     */
    protected $phpExceptionStorage;

    protected function findByHash($hash)
    {
        $model = $this->phpExceptionStorage->findByHash($hash);

        if (!$model) {
            throw new ApiMethodException('Incorrect php exception hash :value', [':value' => $hash]);
        }

        return $model;
    }
}
