<?php
namespace BetaKiller\Widget\Content;

use BetaKiller\Helper\AssetsHelper;
use BetaKiller\Helper\ContentHelper;
use BetaKiller\Helper\ContentUrlContainerHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\UrlHelper;
use BetaKiller\Model\ContentCategoryInterface;
use BetaKiller\Model\ContentPost;
use BetaKiller\Repository\ContentCategoryRepository;
use BetaKiller\Repository\ContentPostRepository;
use BetaKiller\Search\SearchResultsInterface;
use BetaKiller\Url\ZoneInterface;
use BetaKiller\Widget\AbstractPublicWidget;
use HTML;
use Psr\Http\Message\ServerRequestInterface;

final class ArticlesListWidget extends AbstractPublicWidget
{
    private const CATEGORY_ID_QUERY_KEY = 'category-id';
    private const PAGE_QUERY_KEY        = 'page';
    private const SEARCH_TERM_QUERY_KEY = 'term';

    /**
     * @var \BetaKiller\Helper\ContentHelper
     */
    private $contentHelper;

    /**
     * @var \BetaKiller\Repository\ContentPostRepository
     */
    private $postRepo;

    /**
     * @var \BetaKiller\Repository\ContentCategoryRepository
     */
    private $categoryRepo;

    /**
     * @var \BetaKiller\Helper\AssetsHelper
     */
    private $assetsHelper;

    /**
     * @var int
     */
    private $itemsPerPage = 12;

    /**
     * ArticlesListWidget constructor.
     *
     * @param \BetaKiller\Helper\ContentHelper                 $contentHelper
     * @param \BetaKiller\Repository\ContentPostRepository     $postRepo
     * @param \BetaKiller\Repository\ContentCategoryRepository $categoryRepo
     * @param \BetaKiller\Helper\AssetsHelper                  $assetsHelper
     */
    public function __construct(
        ContentHelper $contentHelper,
        ContentPostRepository $postRepo,
        ContentCategoryRepository $categoryRepo,
        AssetsHelper $assetsHelper
    ) {
        $this->contentHelper = $contentHelper;
        $this->postRepo      = $postRepo;
        $this->categoryRepo  = $categoryRepo;
        $this->assetsHelper  = $assetsHelper;
    }

    /**
     * Returns data for View rendering
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @param array                                    $context
     *
     * @return array
     * @throws \Kohana_Exception
     */
    public function getData(ServerRequestInterface $request, array $context): array
    {
        $category = ContentUrlContainerHelper::getContentCategory($request);

        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        $term = $context['term'] ?? null;

        return $this->getArticlesData($urlHelper, $category, null, $term);
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public function actionMore(ServerRequestInterface $request): void
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        $this->response->contentTypeJson();

        if (!$this->request->is_ajax()) {
            $this->response->sendErrorJson();

            return;
        }

        $term       = HTML::chars(strip_tags($this->request->query(self::SEARCH_TERM_QUERY_KEY)));
        $page       = (int)$this->request->query(self::PAGE_QUERY_KEY);
        $categoryID = (int)$this->request->query(self::CATEGORY_ID_QUERY_KEY);

        $category = $this->categoryRepo->findById($categoryID);

        $data = $this->getArticlesData($urlHelper, $category, $page, $term);

        $this->response->sendSuccessJson($data);
    }

    /**
     * @param \BetaKiller\Helper\UrlHelper               $helper
     * @param \BetaKiller\Model\ContentCategoryInterface $category
     * @param int|null                                   $page
     * @param null|string                                $term
     *
     * @return array
     * @throws \BetaKiller\Assets\Exception\AssetsException
     * @throws \BetaKiller\Assets\Exception\AssetsStorageException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \Kohana_Exception
     */
    protected function getArticlesData(
        UrlHelper $helper,
        ?ContentCategoryInterface $category,
        ?int $page = null,
        ?string $term = null
    ): array {
        $postsData = [];

        $page = $page ?: 1;

        $results = $this->getArticles($page, $category, $term);

        /** @var ContentPost[] $articles */
        $articles = $results->getItems();

        foreach ($articles as $article) {
            $thumbnail = $article->getFirstThumbnail();

            $postsData[] = [
                'thumbnail'  => [
                    'original' => $this->assetsHelper->getAttributesForImgTag($thumbnail, $thumbnail::SIZE_ORIGINAL),
                    'preview'  => $this->assetsHelper->getAttributesForImgTag($thumbnail, $thumbnail::SIZE_PREVIEW),
                ],
                'url'        => $helper->getReadEntityUrl($article, ZoneInterface::PUBLIC),
                'label'      => $article->getLabel(),
                'title'      => $article->getTitle(),
                'text'       => $this->contentHelper->getPostContentPreview($article),
                'created_at' => $article->getCreatedAt()->format('d.m.Y'),
            ];
        }

        if ($results->hasNextPage()) {
            $urlParams = [
                self::SEARCH_TERM_QUERY_KEY => $term,
                self::PAGE_QUERY_KEY        => $page + 1,
                self::CATEGORY_ID_QUERY_KEY => $category ? $category->getID() : null,
            ];

            $moreURL = $this->url('more').'?'.http_build_query(array_filter($urlParams));
        } else {
            $moreURL = null;
        }

        return [
            'articles' => $postsData,
            'moreURL'  => $moreURL,
        ];
    }

    /**
     * @param                                        $page
     * @param \BetaKiller\Model\ContentCategoryInterface|null $category
     * @param null                                   $term
     *
     * @return \BetaKiller\Search\SearchResultsInterface
     */
    protected function getArticles($page, ContentCategoryInterface $category = null, $term = null): SearchResultsInterface
    {
        return $this->postRepo->searchArticles($page, $this->itemsPerPage, $category, $term);
    }
}
