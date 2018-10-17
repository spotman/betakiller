<?php
namespace BetaKiller\Widget\Content;

use BetaKiller\Widget\AbstractPublicWidget;
use DateTime;
use DateTimeInterface;
use Psr\Http\Message\ServerRequestInterface;

class QuotesWidget extends AbstractPublicWidget
{
    /**
     * @Inject
     * @var \BetaKiller\Repository\QuoteRepository
     */
    private $repository;

    /**
     * Returns data for View rendering
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @param array                                    $context
     *
     * @return array
     */
    public function getData(ServerRequestInterface $request, array $context): array
    {
        return $this->getQuoteData();
    }

    public function actionRefresh(): void
    {
        $this->response->contentTypeJson();

        $beforeTimestamp = (int)$this->request->query('before');
        $beforeDate      = $beforeTimestamp ? (new DateTime)->setTimestamp($beforeTimestamp) : null;

        $data = $this->getQuoteData($beforeDate);

        $this->response->sendSuccessJson($data);
    }

    protected function getQuoteData(?DateTimeInterface $beforeDate = null): array
    {
        $quote = $this->repository->getLatestQuote($beforeDate);

        $createdAt          = $quote->getCreatedAt();
        $createdAtTimestamp = $createdAt ? $createdAt->getTimestamp() : null;
        $beforeQuery        = $createdAtTimestamp ? '?before='.$createdAtTimestamp : null;

        return [
            'quote'      => $quote->as_array(),
            'refreshURL' => '/quote-refresh/'.$beforeQuery,
        ];
    }
}
