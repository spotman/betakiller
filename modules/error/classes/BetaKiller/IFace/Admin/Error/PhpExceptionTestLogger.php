<?php
namespace BetaKiller\IFace\Admin\Error;

use BetaKiller\Exception\ServerErrorHttpException;
use BetaKiller\Helper\LoggerHelperTrait;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class PhpExceptionTestLogger extends ErrorAdminBase
{
    use LoggerHelperTrait;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * PhpExceptionTestLogger constructor.
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

        $this->logException($this->logger, $e);

        return [];
    }
}
