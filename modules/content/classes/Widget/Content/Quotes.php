<?php

use BetaKiller\IFace\Widget\AbstractBaseWidget;

class Widget_Content_Quotes extends AbstractBaseWidget
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
        return $this->get_quote_data();
    }

    public function action_refresh(): void
    {
        $this->content_type_json();

        $data = $this->get_quote_data();

        $this->send_success_json($data);
    }

    protected function get_quote_data(): array
    {
        $beforeTimestamp = (int)$this->query('before');
        $beforeDate      = $beforeTimestamp ? (new DateTime)->setTimestamp($beforeTimestamp) : null;

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
