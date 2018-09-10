<?php
namespace BetaKiller\Widget\Content;

use BetaKiller\Model\ContentCategory;
use BetaKiller\Model\ContentCategoryInterface;
use BetaKiller\Model\ContentPost;
use BetaKiller\Search\SearchResultsInterface;
use BetaKiller\Url\ZoneInterface;
use BetaKiller\Widget\AbstractPublicWidget;
use HTML;

class ArticlesListWidget extends AbstractPublicWidget
{
    private const CATEGORY_ID_QUERY_KEY = 'category-id';
    private const PAGE_QUERY_KEY        = 'page';
    private const SEARCH_TERM_QUERY_KEY = 'term';

    /**
     * @Inject
     * @var \BetaKiller\Helper\IFaceHelper
     */
    private $ifaceHelper;

    /**
     * @Inject
     * @var \BetaKiller\Helper\ContentUrlContainerHelper
     */
    private $urlParametersHelper;

    /**
     * @var \BetaKiller\Helper\ContentHelper
     * @Inject
     */
    private $contentHelper;

    /**
     * @var \BetaKiller\Repository\ContentPostRepository
     * @Inject
     */
    private $postRepo;

    /**
     * @var \BetaKiller\Repository\ContentCategoryRepository
     * @Inject
     */
    private $categoryRepo;

    /**
     * @var \BetaKiller\Helper\AssetsHelper
     * @Inject
     */
    private $assetsHelper;

    /**
     * @var int
     */
    protected $itemsPerPage = 12;

    /**
     * Returns data for View rendering
     *
     * @return array
     * @throws \Kohana_Exception
     */
    public function getData(): array
    {
        $category = $this->urlParametersHelper->getContentCategory();

        return $this->getArticlesData($category);
    }

    /**
     * @throws \Kohana_Exception
     */
    public function actionMore(): void
    {
        $this->content_type_json();

        if (!$this->is_ajax()) {
            $this->send_error_json();

            return;
        }

        $term       = HTML::chars(strip_tags($this->query(self::SEARCH_TERM_QUERY_KEY)));
        $page       = (int)$this->query(self::PAGE_QUERY_KEY);
        $categoryID = (int)$this->query(self::CATEGORY_ID_QUERY_KEY);

        $category = $this->categoryRepo->findById($categoryID);

        $data = $this->getArticlesData($category, $page, $term);

        $this->send_success_json($data);
    }

    /**
     * @param \BetaKiller\Model\ContentCategoryInterface $category
     * @param int|null                                   $page
     * @param null|string                                $term
     *
     * @return array
     * @throws \Kohana_Exception
     */
    protected function getArticlesData(
        ?ContentCategoryInterface $category,
        ?int $page = null,
        ?string $term = null
    ): array {
        $postsData = [];

        $page = $page ?: 1;

        $results = $this->getArticles($page, $category, $this->getContextTerm() ?: $term);

        /** @var ContentPost[] $articles */
        $articles = $results->getItems();

        foreach ($articles as $article) {
            $thumbnail = $article->getFirstThumbnail();

            $postsData[] = [
                'thumbnail'  => [
                    'original' => $this->assetsHelper->getAttributesForImgTag($thumbnail, $thumbnail::SIZE_ORIGINAL),
                    'preview'  => $this->assetsHelper->getAttributesForImgTag($thumbnail, $thumbnail::SIZE_PREVIEW),
                ],
                'url'        => $this->ifaceHelper->getReadEntityUrl($article, ZoneInterface::PUBLIC),
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

    protected function getContextTerm(): ?string
    {
        return $this->getContextParam('term');
    }

    /**
     * @param                                        $page
     * @param \BetaKiller\Model\ContentCategory|null $category
     * @param null                                   $term
     *
     * @return \BetaKiller\Search\SearchResultsInterface
     */
    protected function getArticles($page, ContentCategory $category = null, $term = null): SearchResultsInterface
    {
        return $this->postRepo->searchArticles($page, $this->itemsPerPage, $category, $term);
    }
}
