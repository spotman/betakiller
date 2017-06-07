<?php

use BetaKiller\IFace\Widget\AbstractBaseWidget;

abstract class Widget_Content_SidebarArticlesList extends AbstractBaseWidget
{
    /**
     * @Inject
     * @var \BetaKiller\Helper\ContentUrlParametersHelper
     */
    private $urlParametersHelper;

    /**
     * Returns data for View rendering
     *
     * @return array
     */
    public function getData(): array
    {
        $limit      = (int)$this->getContextParam('limit', 5);
        $exclude_id = $this->get_current_article_id();

        $articles = $this->get_articles_list($exclude_id, $limit);

        $data = [];

        foreach ($articles as $article) {
            $data[] = $this->get_article_data($article);
        }

        return [
            'articles' => $data,
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
        $current_article = $this->urlParametersHelper->getContentPost();

        return $current_article ? $current_article->get_id() : null;
    }

    protected function get_article_data(Model_ContentPost $article)
    {
        /** @var \Model_ContentImageElement $thumbnail */
        $thumbnail = $article->getFirstThumbnail();

        return [
            'label'     => $article->getLabel(),
            'thumbnail' => $thumbnail->getAttributesForImgTag($thumbnail::SIZE_PREVIEW),
            'url'       => $this->ifaceHelper->getReadEntityUrl($article),
            'date'      => $article->getCreatedAt()->format('d.m.Y'),
        ];
    }
}
