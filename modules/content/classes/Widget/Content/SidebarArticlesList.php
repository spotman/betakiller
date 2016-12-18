<?php

use BetaKiller\IFace\Widget;

abstract class Widget_Content_SidebarArticlesList extends Widget
{
    use \BetaKiller\Helper\ContentTrait;

    /**
     * Returns data for View rendering
     *
     * @return array
     */
    public function get_data()
    {
        $limit = (int) $this->getContextParam('limit', 5);
        $exclude_id = $this->get_current_article_id();

        $articles = $this->get_articles_list($exclude_id, $limit);

        $data = [];

        foreach ($articles as $article)
        {
            $data[] = $this->get_article_data($article);
        }

        return [
            'articles'  =>  $data,
        ];
    }

    /**
     * @param int $exclude_id
     * @param int $limit
     *
     * @return Model_ContentPost[]
     */
    abstract protected function get_articles_list($exclude_id, $limit);

    protected function get_current_article_id()
    {
        $current_article = $this->url_parameter_content_post();

        return $current_article ? $current_article->get_id() : NULL;
    }

    protected function get_article_data(Model_ContentPost $article)
    {
        /** @var \Model_ContentImageElement $thumbnail */
        $thumbnail = $article->get_first_thumbnail();

        return [
            'label'     =>  $article->get_label(),
            'thumbnail' =>  $thumbnail->get_attributes_for_img_tag($thumbnail::SIZE_PREVIEW),
            'url'       =>  $article->get_public_url(),
            'date'      =>  $article->get_created_at()->format('d.m.Y'),
        ];
    }
}
