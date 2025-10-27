<?php

namespace BetaKiller\IFace\Admin\Test;

use BetaKiller\Exception\ServerErrorHttpException;
use BetaKiller\Helper\LoggerHelper;
use BetaKiller\IFace\Admin\Error\AbstractErrorAdminIFace;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

readonly class PhpExceptionLoggerIFace extends AbstractErrorAdminIFace
{
    /**
     * PhpExceptionLogger constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(private LoggerInterface $logger)
    {
    }

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     */
    public function getData(ServerRequestInterface $request): array
    {
        $e = new ServerErrorHttpException('Test exception for logger');

        LoggerHelper::logRequestException($this->logger, $e, $request);

        return [];
    }
}
