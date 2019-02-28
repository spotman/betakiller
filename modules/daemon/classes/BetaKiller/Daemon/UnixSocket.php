<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use Psr\Log\LoggerInterface;

class UnixSocket
{
    private const TIMEOUT_ACQUIRE = 3;
    private const TIMEOUT_RELEASE = 3;

    /**
     * @var string
     */
    private $path;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Lock constructor.
     *
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function getPath(): string
    {
        return $this->path;
    }

}
