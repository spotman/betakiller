<?php

use BetaKiller\IFace\Widget\BaseWidget;

class Widget_Content_ArticlesList extends BaseWidget
{
    use \BetaKiller\Helper\ContentTrait;

    const CATEGORY_ID_QUERY_KEY = 'category-id';
    const PAGE_QUERY_KEY = 'page';
    const SEARCH_TERM_QUERY_KEY = 'term';

    protected $items_per_page = 12;

    /**
     * Returns data for View rendering
     *
     * @return array
     */
    public function getData()
    {
        return $this->get_articles_data();
    }

    public function action_more()
    {
        $this->content_type_json();

        $data = $this->get_articles_data();

        $this->send_success_json($data);
    }

    protected function get_articles_data()
    {
        $posts_data = [];

        $page = (int) $this->query(self::PAGE_QUERY_KEY) ?: 1;
        $term = $this->get_search_term();

        $category = $this->get_category();
        $results = $this->get_articles($page, $category, $term);

        /** @var Model_ContentPost[] $articles */
        $articles = $results->getItems();
        $index = 1;

        foreach ($articles as $article) {
            $is_large = ($index % 3 === 1);

            $thumbnail = $article->getFirstThumbnail();
            $thumbnail_size = $is_large ? $thumbnail::SIZE_ORIGINAL : $thumbnail::SIZE_PREVIEW;

            $posts_data[] = [
                'is_large'      =>  $is_large,
                'thumbnail'     =>  $thumbnail->getAttributesForImgTag($thumbnail_size),
                'url'           =>  $article->get_public_url(),
                'label'         =>  $article->getLabel(),
                'title'         =>  $article->getTitle(),
                'text'          =>  $article->getContentPreview(),
                'created_at'    =>  $article->getCreatedAt()->format("d.m.Y"),
            ];

            $index++;
        }

        if ($results->hasNextPage()) {
            $url_params = [
                self::SEARCH_TERM_QUERY_KEY     =>  $term,
                self::PAGE_QUERY_KEY            =>  $page + 1,
                self::CATEGORY_ID_QUERY_KEY     =>  $category ? $category->get_id() : null,
            ];

            $moreURL = $this->url('more').'?'.http_build_query(array_filter($url_params));
        } else {
            $moreURL = null;
        }

        return [
            'articles'  =>  $posts_data,
            'moreURL'   =>  $moreURL,
        ];
    }

    protected function get_category()
    {
        if ($this->is_ajax()) {
            $category_id = (int) $this->query(self::CATEGORY_ID_QUERY_KEY);

            return $this->model_factory_content_category($category_id);
        }

        return $this->url_parameter_content_category();
    }

    protected function get_search_term()
    {
        return $this->getContextParam('term') ?: HTML::chars(strip_tags($this->query(self::SEARCH_TERM_QUERY_KEY)));
    }

    protected function get_articles($page, Model_ContentCategory $category = null, $term = null)
    {
        $posts_orm = $this->model_factory_content_post();

        if ($category && $category->get_id()) {
            $categories_ids = $category->get_all_related_categories_ids();
            $posts_orm->filterCategoryIDs($categories_ids);
        }

        if ($term) {
            $posts_orm->search($term);
        }

        return $posts_orm->filterArticles()->orderByCreatedAt()->getSearchResults($page, $this->items_per_page);
    }
}
