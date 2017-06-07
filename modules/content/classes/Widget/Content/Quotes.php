<?php

use BetaKiller\IFace\Widget\AbstractBaseWidget;

class Widget_Content_Quotes extends AbstractBaseWidget
{
    use \BetaKiller\Helper\ContentTrait;

    /**
     * Returns data for View rendering
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->get_quote_data();
    }

    public function action_refresh()
    {
        $this->content_type_json();

        $data = $this->get_quote_data();

        $this->send_success_json($data);
    }

    protected function get_quote_data()
    {
        $orm = $this->model_factory_quote();

        $before_timestamp = (int) $this->query('before');

        $before_date = $before_timestamp ? (new DateTime)->setTimestamp($before_timestamp) : NULL;
        $quote = $orm->get_latest_quote($before_date);

        $createdAt = $quote->get_created_at();
        $createdAtTimestamp = $createdAt ? $createdAt->getTimestamp() : null;
        $beforeQuery = $createdAtTimestamp ? '?before='.$createdAtTimestamp : null;

        return [
            'quote'         =>  $quote->as_array(),
            'refreshURL'    =>  $this->url('refresh').$beforeQuery,
        ];
    }
}
