<?php

class Model_ContentPage extends Model_ContentItem
{
    /**
     * @uses \V2017\IFace\ContentPageItem
     * @return string
     */
    public function get_public_url_iface_codename()
    {
        return 'ContentPageItem';
    }

    /**
     * @param URL_Parameters $parameters
     */
    protected function custom_find_by_url_filter(URL_Parameters $parameters)
    {
        $this->filter_type(self::TYPE_PAGE);

        parent::custom_find_by_url_filter($parameters);
    }
}
