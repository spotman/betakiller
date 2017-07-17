<?php
namespace BetaKiller\Log;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

class LoggerHelper
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    use LoggerTrait;

    /**
     * LoggerHelper constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function logException(\Throwable $e): void
    {
        $this->logger->alert(':message at :file::line', [
            ':message'  => $e->getMessage(),
            ':file'     => $e->getFile(),
            ':line'     => $e->getLine(),
            'exception' => $e,
        ]);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function log($level, string $message, array $context = null): void
    {
        $this->logger->log($level, $message, $context ?? []);
    }
}
