<?php
declare(strict_types=1);

namespace BetaKiller\Security;

use BetaKiller\Exception\BadRequestHttpException;
use BetaKiller\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class CspReportHandler implements RequestHandlerInterface
{
    public const URL = '/csp-report-handler';

    private const IGNORED_BLOCKED_URI = [
        /** @see https://stackoverflow.com/a/35559407 */
        'about',
    ];

    private const IGNORED_DOCUMENT_URI = [
        /** @see https://stackoverflow.com/a/35559407 */
        'about',
    ];

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
        $report = \json_decode($request->getBody()->getContents(), true, 10, JSON_THROW_ON_ERROR);

        if (empty($report) || !isset($report['csp-report'])) {
            throw new BadRequestHttpException;
        }

        $data = $report['csp-report'];

        $blockedUri = $data['blocked-uri'];

        if (\in_array($blockedUri, self::IGNORED_BLOCKED_URI, true)) {
            return ResponseHelper::text('Ignored');
        }

        $documentUrl = $data['document-uri'];

        if (\in_array($documentUrl, self::IGNORED_DOCUMENT_URI, true)) {
            return ResponseHelper::text('Ignored');
        }

        $this->logger->alert('CSP ":directive" blocked ":blocked" at :url', [
            ':blocked'   => $blockedUri,
            ':directive' => $data['effective-directive'] ?? ($data['violated-directive'] ?? 'unknown'),
            ':url'       => $documentUrl,
        ]);

        return ResponseHelper::text('OK');
    }
}
