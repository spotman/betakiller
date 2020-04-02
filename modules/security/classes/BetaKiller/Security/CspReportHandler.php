<?php
declare(strict_types=1);

namespace BetaKiller\Security;

use BetaKiller\Exception\BadRequestHttpException;
use BetaKiller\Exception\SecurityException;
use BetaKiller\Helper\LoggerHelper;
use BetaKiller\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriFactoryInterface;
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
     * @var \Psr\Http\Message\UriFactoryInterface
     */
    private $uriFactory;

    /**
     * CspReportHandler constructor.
     *
     * @param \Psr\Http\Message\UriFactoryInterface $uriFactory
     * @param \Psr\Log\LoggerInterface              $logger
     */
    public function __construct(UriFactoryInterface $uriFactory, LoggerInterface $logger)
    {
        $this->logger     = $logger;
        $this->uriFactory = $uriFactory;
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
        $report = \json_decode($request->getBody()->getContents(), true);

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

        $e = new SecurityException('CSP: ":directive" blocked ":blocked"', [
            ':blocked'   => $blockedUri,
            ':directive' => $data['effective-directive'] ?? ($data['violated-directive'] ?? 'unknown'),
        ]);

        $uri = $this->uriFactory->createUri($documentUrl);

        LoggerHelper::logException($this->logger, $e, null, $request->withUri($uri));

        return ResponseHelper::text('OK');
    }
}
