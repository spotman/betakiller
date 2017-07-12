<?php

use BetaKiller\IFace\Widget\AbstractBaseWidget;
use BetaKiller\Model\ContentCategory;
use BetaKiller\Model\ContentPost;
use BetaKiller\Model\IFaceZone;
use BetaKiller\Search\SearchResultsInterface;

class Widget_Content_ArticlesList extends AbstractBaseWidget
{
    const CATEGORY_ID_QUERY_KEY = 'category-id';
    const PAGE_QUERY_KEY        = 'page';
    const SEARCH_TERM_QUERY_KEY = 'term';

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
     * @var \BetaKiller\Helper\AssetsHelper
     * @Inject
     */
    private $assetsHelper;

    protected $itemsPerPage = 12;

    /**
     * Returns data for View rendering
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->get_articles_data();
    }

    public function action_more(): void
    {
        $this->content_type_json();

        $data = $this->get_articles_data();

        $this->send_success_json($data);
    }

    protected function get_articles_data(): array
    {
        $posts_data = [];

        $page = (int)$this->query(self::PAGE_QUERY_KEY) ?: 1;
        $term = $this->get_search_term();

        $category = $this->get_category();
        $results  = $this->get_articles($page, $category, $term);

        /** @var ContentPost[] $articles */
        $articles = $results->getItems();

        foreach ($articles as $article) {
            $thumbnail = $article->getFirstThumbnail();

            $posts_data[] = [
                'thumbnail'  => [
                    'original' => $this->assetsHelper->getAttributesForImgTag($thumbnail, $thumbnail::SIZE_ORIGINAL),
                    'preview'  => $this->assetsHelper->getAttributesForImgTag($thumbnail, $thumbnail::SIZE_PREVIEW),
                ],
                'url'        => $this->ifaceHelper->getReadEntityUrl($article, IFaceZone::PUBLIC_ZONE),
                'label'      => $article->getLabel(),
                'title'      => $article->getTitle(),
                'text'       => $this->contentHelper->getPostContentPreview($article),
                'created_at' => $article->getCreatedAt()->format('d.m.Y'),
            ];
        }

        if ($results->hasNextPage()) {
            $url_params = [
                self::SEARCH_TERM_QUERY_KEY => $term,
                self::PAGE_QUERY_KEY        => $page + 1,
                self::CATEGORY_ID_QUERY_KEY => $category ? $category->get_id() : null,
            ];

            $moreURL = $this->url('more').'?'.http_build_query(array_filter($url_params));
        } else {
            $moreURL = null;
        }

        return [
            'articles' => $posts_data,
            'moreURL'  => $moreURL,
        ];
    }

    protected function get_category()
    {
        if ($this->is_ajax()) {
            $categoryID = (int)$this->query(self::CATEGORY_ID_QUERY_KEY);

            $categoryRepo = $this->contentHelper->getCategoryRepository();

            return $categoryRepo->findById($categoryID);
        }

        return $this->urlParametersHelper->getContentCategory();
    }

    protected function get_search_term(): string
    {
        return $this->getContextParam('term') ?: HTML::chars(strip_tags($this->query(self::SEARCH_TERM_QUERY_KEY)));
    }

    /**
     * @param                                        $page
     * @param \BetaKiller\Model\ContentCategory|null $category
     * @param null                                   $term
     *
     * @return \BetaKiller\Search\SearchResultsInterface
     */
    protected function get_articles($page, ContentCategory $category = null, $term = null): SearchResultsInterface
    {
        $postRepo = $this->contentHelper->getPostRepository();

        return $postRepo->searchArticles($page, $this->itemsPerPage, $category, $term);
    }
}
