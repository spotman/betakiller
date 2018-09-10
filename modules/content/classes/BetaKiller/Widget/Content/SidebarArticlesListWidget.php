<?php
namespace BetaKiller\Widget\Content;

use BetaKiller\Helper\AssetsHelper;
use BetaKiller\Helper\IFaceHelper;
use BetaKiller\Model\ContentPostInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\ZoneInterface;
use BetaKiller\Widget\AbstractPublicWidget;

abstract class SidebarArticlesListWidget extends AbstractPublicWidget
{
    /**
     * @var \BetaKiller\Url\Container\UrlContainerInterface
     */
    private $urlContainer;

    /**
     * @var \BetaKiller\Helper\AssetsHelper
     */
    private $assetsHelper;

    /**
     * @var \BetaKiller\Helper\IFaceHelper
     */
    private $ifaceHelper;

    /**
     * SidebarArticlesListWidget constructor.
     *
     * @param \BetaKiller\Url\Container\UrlContainerInterface $urlContainer
     * @param \BetaKiller\Helper\AssetsHelper                 $assetsHelper
     * @param \BetaKiller\Helper\IFaceHelper                  $ifaceHelper
     */
    public function __construct(
        UrlContainerInterface $urlContainer,
        AssetsHelper $assetsHelper,
        IFaceHelper $ifaceHelper
    ) {
        $this->urlContainer = $urlContainer;
        $this->assetsHelper = $assetsHelper;
        $this->ifaceHelper  = $ifaceHelper;
    }

    /**
     * Returns data for View rendering
     *
     * @return array
     */
    public function getData(): array
    {
        $limit     = (int)$this->getContextParam('limit', 5);
        $excludeID = $this->getCurrentArticleID();

        $articles = $this->getArticlesList($excludeID, $limit);

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
        /** @var ContentPostInterface|null $currentArticle */
        $currentArticle = $this->urlContainer->getEntityByClassName(ContentPostInterface::class);

        return $currentArticle ? $currentArticle->getID() : null;
    }

    protected function getArticleData(ContentPostInterface $article): array
    {
        $thumbnail = $article->getFirstThumbnail();
        $createdAt = $article->getCreatedAt();

        return [
            'label'     => $article->getLabel(),
            'thumbnail' => $this->assetsHelper->getAttributesForImgTag($thumbnail, $thumbnail::SIZE_PREVIEW),
            'url'       => $this->ifaceHelper->getReadEntityUrl($article, ZoneInterface::PUBLIC),
            'date'      => $createdAt ? $createdAt->format('d.m.Y') : null,
        ];
    }
}
