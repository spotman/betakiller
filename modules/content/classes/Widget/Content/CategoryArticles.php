<?php

use BetaKiller\IFace\Widget;
use BetaKiller\IFace\Widget\Exception;

class Widget_Content_CategoryArticles extends Widget
{
    use \BetaKiller\Helper\ContentTrait;

    const CATEGORY_ID_QUERY_KEY = 'category-id';
    const BEFORE_TIMESTAMP_QUERY_KEY = 'before';

    protected $items_per_page = 12;

    /**
     * Returns data for View rendering
     *
     * @return array
     */
    public function get_data()
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
        $latest_post_created_at = null;
        $posts_data = [];

        $category = $this->get_category();
        $articles = $this->get_category_articles($category);
        $index = 1;

        foreach ($articles as $article) {

            $created_at = $article->get_created_at();

            if (!$latest_post_created_at || $created_at < $latest_post_created_at) {
                $latest_post_created_at = $created_at;
            }

            $is_large = ($index % 3 === 1);

            $thumbnail = $article->get_first_thumbnail();
            $thumbnail_size = $is_large ? $thumbnail::SIZE_ORIGINAL : $thumbnail::SIZE_PREVIEW;

            $posts_data[] = [
                'is_large'      =>  $is_large,
                'thumbnail'     =>  $thumbnail->get_attributes_for_img_tag($thumbnail_size),
                'url'           =>  $article->get_public_url(),
                'label'         =>  $article->get_label(),
                'title'         =>  $article->get_title(),
                'text'          =>  Text::limit_chars(strip_tags($article->get_content()), 200, '...', true),
                'created_at'    =>  $article->get_created_at()->format("d.m.Y"),
            ];

            $index++;
        }

        if ($latest_post_created_at && count($articles) == $this->items_per_page) {
            $url_params = [
                self::CATEGORY_ID_QUERY_KEY         =>  $category->get_id(),
                self::BEFORE_TIMESTAMP_QUERY_KEY    =>  $latest_post_created_at->getTimestamp(),
            ];

            $moreURL = $this->url('more').'?'.http_build_query($url_params);
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

            if (!$category_id) {
                throw new Exception('Empty category id');
            }

            return $this->model_factory_content_category($category_id);
        } else {
            return $this->url_parameter_content_category();
        }
    }

    protected function get_category_articles(Model_ContentCategory $category)
    {
        $before_timestamp = (int) $this->query(self::BEFORE_TIMESTAMP_QUERY_KEY);

        $before_date = new DateTime;

        if ($before_timestamp) {
            $before_date->setTimestamp($before_timestamp);
        }

        return $category->get_all_related_articles_before($before_date, $this->items_per_page);
    }
}
