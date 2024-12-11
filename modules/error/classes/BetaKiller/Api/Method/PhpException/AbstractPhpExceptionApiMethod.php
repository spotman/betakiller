<?php
namespace BetaKiller\Api\Method\PhpException;

use BetaKiller\Model\PhpExceptionModelInterface;
use BetaKiller\Repository\PhpExceptionRepository;
use Spotman\Api\ApiMethodException;
use Spotman\Api\Method\AbstractApiMethod;

abstract readonly class AbstractPhpExceptionApiMethod extends AbstractApiMethod
{
    protected function findByHash(PhpExceptionRepository $repository, string $hash): PhpExceptionModelInterface
    {
        $model = $repository->findByHash($hash);

        if (!$model) {
            throw new ApiMethodException('Incorrect php exception hash :value', [':value' => $hash]);
        }

        return $model;
    }
}
