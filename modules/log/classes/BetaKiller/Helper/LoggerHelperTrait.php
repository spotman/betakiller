<?php
namespace BetaKiller\Helper;

use Psr\Log\LoggerInterface;

trait LoggerHelperTrait
{
    final public function logException(LoggerInterface $logger, \Throwable $e): void
    {
        $logger->alert(':message at :file::line', [
            ':message'  => $e->getMessage(),
            ':file'     => $e->getFile(),
            ':line'     => $e->getLine(),
            'exception' => $e,
        ]);
    }
}
