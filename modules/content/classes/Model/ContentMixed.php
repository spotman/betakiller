<?php

class Model_ContentMixed extends Model_ContentItem
{
//    const URL_PARAM = 'ContentMixed';

    /**
     * @uses BetaKiller\Content\IFace\ContentMixedItem
     */
    public function get_public_url_iface_codename()
    {
        // TODO Выпилить множественные модели и реализовать кастомный url behaviour

        return 'ContentMixedItem';
    }

    /**
     * @param URL_Parameters $parameters
     */
    protected function custom_find_by_url_filter(URL_Parameters $parameters)
    {
        // Load pages first
        $this->prioritize_by_post_types();

        parent::custom_find_by_url_filter($parameters);
    }
}
