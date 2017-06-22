<?php
namespace BetaKiller\Api\Method\PhpException;

use BetaKiller\Error\PhpExceptionModelInterface;
use Spotman\Api\ApiMethodException;
use Spotman\Api\Method\AbstractApiMethod;

abstract class AbstractPhpExceptionApiMethod extends AbstractApiMethod
{
    /**
     * @Inject
     * @var \BetaKiller\Error\PhpExceptionStorageInterface
     */
    protected $phpExceptionStorage;

    protected function findByHash($hash): PhpExceptionModelInterface
    {
        $model = $this->phpExceptionStorage->findByHash($hash);

        if (!$model) {
            throw new ApiMethodException('Incorrect php exception hash :value', [':value' => $hash]);
        }

        return $model;
    }
}
