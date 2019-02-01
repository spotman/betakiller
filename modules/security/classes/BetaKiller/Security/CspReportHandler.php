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
use Psr\Http\Message\UriFactoryInterface;
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
        if (ServerRequestHelper::getContentType($request) !== 'application/csp-report') {
            throw new BadRequestHttpException;
        }

        $report = \json_decode($request->getBody()->getContents(), true);

        if (empty($report) || !isset($report['csp-report'])) {
            throw new BadRequestHttpException;
        }

        $data = $report['csp-report'];

        $url = $data['document-uri'];
        $uri = $this->uriFactory->createUri($url);

        $e = new SecurityException('SCP violation for ":blocked" with directive ":directive" in ":sample"', [
            ':blocked'   => $data['blocked-uri'],
            ':directive' => $data['violated-directive'],
            ':sample'    => !empty($data['script-sample']) ? $data['script-sample'] : '__no-sample__',
        ]);

        $this->logException($this->logger, $e, $request->withUri($uri));

        return ResponseHelper::text('OK');
    }
}
