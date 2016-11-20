<?php
namespace BetaKiller\Content\IFace;


class ContentMixedItem extends ContentPostBase
{
    /**
     * @return string
     */
    protected function get_content_model_url_key()
    {
        return \Model_ContentMixed::URL_PARAM;
    }
}
