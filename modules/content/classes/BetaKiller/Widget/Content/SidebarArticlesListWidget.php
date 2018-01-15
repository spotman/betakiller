<?php
namespace BetaKiller\Widget\Content;

use BetaKiller\Model\ContentPostInterface;
use BetaKiller\Model\IFaceZone;
use BetaKiller\Widget\AbstractBaseWidget;

abstract class SidebarArticlesListWidget extends AbstractBaseWidget
{
    /**
     * @Inject
     * @var \BetaKiller\Helper\ContentUrlContainerHelper
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

        $articles = $this->getArticlesList($exclude_id, $limit);

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
     * @return ContentPostInterface[]
     */
    abstract protected function getArticlesList($exclude_id, $limit): array;

    protected function getCurrentArticleID()
    {
        $current_article = $this->urlParametersHelper->getContentPost();

        return $current_article ? $current_article->getID() : null;
    }

    protected function getArticleData(ContentPostInterface $article): array
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
