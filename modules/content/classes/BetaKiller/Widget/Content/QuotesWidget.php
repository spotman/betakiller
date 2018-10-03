<?php
namespace BetaKiller\Widget\Content;

use BetaKiller\Widget\AbstractPublicWidget;
use DateTime;
use DateTimeInterface;

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
     * @return array
     */
    public function getData(): array
    {
        return $this->getQuoteData();
    }

    public function actionRefresh(): void
    {
        $this->response->content_type_json();

        $beforeTimestamp = (int)$this->request->query('before');
        $beforeDate      = $beforeTimestamp ? (new DateTime)->setTimestamp($beforeTimestamp) : null;

        $data = $this->getQuoteData($beforeDate);

        $this->response->send_success_json($data);
    }

    protected function getQuoteData(?DateTimeInterface $beforeDate = null): array
    {
        $quote = $this->repository->getLatestQuote($beforeDate);

        $createdAt          = $quote->getCreatedAt();
        $createdAtTimestamp = $createdAt ? $createdAt->getTimestamp() : null;
        $beforeQuery        = $createdAtTimestamp ? '?before='.$createdAtTimestamp : null;

        return [
            'quote'      => $quote->as_array(),
            'refreshURL' => $this->url('refresh').$beforeQuery,
        ];
    }
}
