<?php

class Model_ContentArticle extends Model_ContentItem
{
    /**
     * @uses BetaKiller\Content\IFace\ContentArticleItem
     */
    public function get_public_url_iface_codename()
    {
        return 'ContentArticleItem';
    }

    /**
     * @param URL_Parameters $parameters
     */
    protected function custom_find_by_url_filter(URL_Parameters $parameters)
    {
        $this->filter_type(self::TYPE_ARTICLE);

        parent::custom_find_by_url_filter($parameters);
    }
}
