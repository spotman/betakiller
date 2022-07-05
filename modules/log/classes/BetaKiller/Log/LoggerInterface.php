<?php
namespace BetaKiller\Log;

use Monolog\Handler\HandlerInterface;

interface LoggerInterface extends \Psr\Log\LoggerInterface
{
    /**
     * @param \Monolog\Handler\HandlerInterface $handler
     */
    public function pushHandler(HandlerInterface $handler): void;

    public function flushBuffers(): void;

    /**
     * @return \Monolog\Logger
     */
    public function getMonologInstance(): \Monolog\Logger;
}
