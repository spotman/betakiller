<?php

declare(strict_types=1);

namespace BetaKiller\Web;

use BetaKiller\Exception;
use BetaKiller\Helper\LoggerHelper;
use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;

readonly class ServerRequestErrorResponseGenerator
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function __invoke(Throwable $e): ResponseInterface
    {
        // Log exception to developers
        $this->logger->alert(Exception::oneLiner($e), [
            LoggerHelper::CONTEXT_KEY_EXCEPTION => $e,
        ]);

        // No exception info here for security reasons
        return new TextResponse('System error', 500);
    }
}
