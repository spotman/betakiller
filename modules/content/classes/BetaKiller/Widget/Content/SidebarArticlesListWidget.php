<?php
namespace BetaKiller\Widget\Content;

use BetaKiller\Helper\AssetsHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\UrlHelper;
use BetaKiller\Model\ContentPostInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\ZoneInterface;
use BetaKiller\Widget\AbstractPublicWidget;
use Psr\Http\Message\ServerRequestInterface;

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
     * SidebarArticlesListWidget constructor.
     *
     * @param \BetaKiller\Url\Container\UrlContainerInterface $urlContainer
     * @param \BetaKiller\Helper\AssetsHelper                 $assetsHelper
     */
    public function __construct(
        UrlContainerInterface $urlContainer,
        AssetsHelper $assetsHelper
    ) {
        $this->urlContainer = $urlContainer;
        $this->assetsHelper = $assetsHelper;
    }

    /**
     * Returns data for View rendering
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param array                                    $context
     *
     * @return array
     * @throws \BetaKiller\Assets\AssetsException
     * @throws \BetaKiller\Assets\AssetsStorageException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getData(ServerRequestInterface $request, array $context): array
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        $limit     = (int)$context['limit'] ?: 5;
        $excludeID = $this->getCurrentArticleID();

        $articles = $this->getArticlesList($excludeID, $limit);

        $data = [];

        foreach ($articles as $article) {
            $data[] = $this->getArticleData($article, $urlHelper);
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

    protected function getArticleData(ContentPostInterface $article, UrlHelper $urlHelper): array
    {
        $thumbnail = $article->getFirstThumbnail();
        $createdAt = $article->getCreatedAt();

        return [
            'label'     => $article->getLabel(),
            'thumbnail' => $this->assetsHelper->getAttributesForImgTag($thumbnail, $thumbnail::SIZE_PREVIEW),
            'url'       => $urlHelper->getReadEntityUrl($article, ZoneInterface::PUBLIC),
            'date'      => $createdAt ? $createdAt->format('d.m.Y') : null,
        ];
    }
}
