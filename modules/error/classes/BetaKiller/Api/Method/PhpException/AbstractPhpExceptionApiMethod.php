<?php
namespace BetaKiller\Api\Method\PhpException;

use BetaKiller\Error\PhpExceptionModelInterface;
use BetaKiller\Error\PhpExceptionStorageInterface;
use Spotman\Api\ApiMethodException;
use Spotman\Api\Method\AbstractApiMethod;

abstract class AbstractPhpExceptionApiMethod extends AbstractApiMethod
{
    protected function findByHash( PhpExceptionStorageInterface $storage, string $hash): PhpExceptionModelInterface
    {
        $model = $storage->findByHash($hash);

        if (!$model) {
            throw new ApiMethodException('Incorrect php exception hash :value', [':value' => $hash]);
        }

        return $model;
    }
}
