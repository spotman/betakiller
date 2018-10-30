<?php
declare(strict_types=1);

namespace BetaKiller\Security;

use BetaKiller\Exception\BadRequestHttpException;
use BetaKiller\Exception\SecurityException;
use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class CspReportHandler implements RequestHandlerInterface
{
    use LoggerHelperTrait;

    public const URL = '/csp-report-handler';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * CspReportHandler constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Handle the request and return a response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (ServerRequestHelper::getContentType($request) !== 'application/csp-report') {
            throw new BadRequestHttpException;
        }

        $report = \json_decode($request->getBody()->getContents(), true);

        if (empty($report) || !isset($report['csp-report'])) {
            throw new BadRequestHttpException;
        }

        $data = $report['csp-report'];

        $e = new SecurityException('SCP violation for ":blocked" with directive ":directive" at :url', [
            ':blocked'   => $data['blocked-uri'],
            ':directive' => $data['violated-directive'],
            ':url'        => $data['document-uri'],
            ':ip'        => ServerRequestHelper::getIpAddress($request),
        ]);

        $this->logException($this->logger, $e);

        return ResponseHelper::text('OK');
    }
}
