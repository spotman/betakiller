<?php
namespace BetaKiller\Api\Method\PhpException;

use BetaKiller\Helper\ErrorHelperTrait;
use Spotman\Api\ApiMethodException;

trait PhpExceptionMethodTrait
{
    use ErrorHelperTrait;

    protected function findByHash($hash)
    {
        $model = $this->phpExceptionStorageFactory()->findByHash($hash);

        if (!$model) {
            throw new ApiMethodException('Incorrect php exception hash :value', [':value' => $hash]);
        }

        return $model;
    }
}
