<?php
namespace BetaKiller\Widget\Content;

use BetaKiller\Helper\AssetsHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\UrlHelperInterface;
use BetaKiller\Model\ContentPostInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\Zone;
use BetaKiller\Url\ZoneInterface;
use BetaKiller\Widget\AbstractPublicWidget;
use Psr\Http\Message\ServerRequestInterface;

abstract class SidebarArticlesListWidget extends AbstractPublicWidget
{
    /**
     * @var \BetaKiller\Helper\AssetsHelper
     */
    private $assetsHelper;

    /**
     * SidebarArticlesListWidget constructor.
     *
     * @param \BetaKiller\Helper\AssetsHelper $assetsHelper
     */
    public function __construct(AssetsHelper $assetsHelper)
    {
        $this->assetsHelper = $assetsHelper;
    }

    /**
     * Returns data for View rendering
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param array                                    $context
     *
     * @return array
     * @throws \BetaKiller\Assets\Exception\AssetsException
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function getData(ServerRequestInterface $request, array $context): array
    {
        $urlHelper    = ServerRequestHelper::getUrlHelper($request);
        $urlContainer = ServerRequestHelper::getUrlContainer($request);

        $limit     = (int)$context['limit'] ?: 5;
        $excludeID = $this->getCurrentArticleID($urlContainer);

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

    protected function getCurrentArticleID(UrlContainerInterface $urlContainer): ?string
    {
        /** @var ContentPostInterface|null $currentArticle */
        $currentArticle = $urlContainer->getEntityByClassName(ContentPostInterface::class);

        return $currentArticle?->getID();
    }

    protected function getArticleData(ContentPostInterface $article, UrlHelperInterface $urlHelper): array
    {
        $thumbnail = $article->getFirstThumbnail();
        $createdAt = $article->getCreatedAt();

        return [
            'label'     => $article->getLabel(),
            'thumbnail' => $this->assetsHelper->getAttributesForImgTag($thumbnail, $thumbnail::SIZE_PREVIEW),
            'url'       => $urlHelper->getReadEntityUrl($article, Zone::public()),
            'date'      => $createdAt?->format('d.m.Y'),
        ];
    }
}
