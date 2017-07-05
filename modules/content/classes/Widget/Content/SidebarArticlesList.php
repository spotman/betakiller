<?php

use BetaKiller\IFace\Widget\AbstractBaseWidget;
use BetaKiller\Model\ContentPost;
use BetaKiller\Model\IFaceZone;

abstract class Widget_Content_SidebarArticlesList extends AbstractBaseWidget
{
    /**
     * @Inject
     * @var \BetaKiller\Helper\ContentUrlParametersHelper
     */
    private $urlParametersHelper;

    /**
     * @var \BetaKiller\Helper\AssetsHelper
     * @Inject
     */
    private $assetsHelper;

    /**
     * Returns data for View rendering
     *
     * @return array
     */
    public function getData(): array
    {
        $limit      = (int)$this->getContextParam('limit', 5);
        $exclude_id = $this->getCurrentArticleID();

        $articles = $this->get_articles_list($exclude_id, $limit);

        $data = [];

        foreach ($articles as $article) {
            $data[] = $this->getArticleData($article);
        }

        return [
            'articles' => $data,
        ];
    }

    /**
     * @param int $exclude_id
     * @param int $limit
     *
     * @return ContentPost[]
     */
    abstract protected function get_articles_list($exclude_id, $limit);

    protected function getCurrentArticleID()
    {
        $current_article = $this->urlParametersHelper->getContentPost();

        return $current_article ? $current_article->get_id() : null;
    }

    protected function getArticleData(ContentPost $article): array
    {
        /** @var \BetaKiller\Model\ContentImage $thumbnail */
        $thumbnail = $article->getFirstThumbnail();

        $createdAt = $article->getCreatedAt();

        return [
            'label'     => $article->getLabel(),
            'thumbnail' => $this->assetsHelper->getAttributesForImgTag($thumbnail, $thumbnail::SIZE_PREVIEW),
            'url'       => $this->ifaceHelper->getReadEntityUrl($article, IFaceZone::PUBLIC_ZONE),
            'date'      => $createdAt ? $createdAt->format('d.m.Y') : null,
        ];
    }
}
