<?php
namespace BetaKiller\Helper;

use Psr\Log\LoggerInterface;

trait LoggerHelperTrait
{
    final protected function logException(LoggerInterface $logger, \Throwable $e, string $message = null): void
    {
        if ($message) {
            $message .= ': '.$e->getMessage();
        } else {
            $message = $e->getMessage();
        }

        $logger->alert(':message at :file::line', [
            ':message'  => $message,
            ':file'     => $e->getFile(),
            ':line'     => $e->getLine(),
            'exception' => $e,
        ]);
    }
}
