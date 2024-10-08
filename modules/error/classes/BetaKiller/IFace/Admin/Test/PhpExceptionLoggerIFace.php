<?php
namespace BetaKiller\IFace\Admin\Test;

use BetaKiller\Exception\ServerErrorHttpException;
use BetaKiller\Helper\LoggerHelper;
use BetaKiller\IFace\Admin\Error\AbstractErrorAdminIFace;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class PhpExceptionLoggerIFace extends AbstractErrorAdminIFace
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * PhpExceptionLogger constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
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
        $e = new ServerErrorHttpException();

        LoggerHelper::logRequestException($this->logger, $e, $request);

        return [];
    }
}
