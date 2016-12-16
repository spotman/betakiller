<?php

use BetaKiller\IFace\Widget;

class Widget_Content_CategoryArticles extends Widget
{
    use \BetaKiller\Helper\ContentTrait;

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

        foreach ($this->get_category_articles() as $article) {

            $created_at = $article->get_created_at();

            if (!$latest_post_created_at || $created_at > $latest_post_created_at) {
                $latest_post_created_at = $created_at;
            }

            $thumbnail = $article->get_first_thumbnail();

            $posts_data[] = [
                'thumbnail' =>  $thumbnail->get_arguments_for_img_tag($thumbnail::SIZE_PREVIEW),
                'url'       =>  $article->get_public_url(),
                'label'     =>  $article->get_label(),
                'text'      =>  strip_tags($article->get_content()),
            ];
        }

        return [
            'articles'  =>  $posts_data,
            'moreURL'   =>  $this->url('more').'?before='.$latest_post_created_at->getTimestamp(),
        ];
    }

    protected function get_category_articles()
    {
        $category = $this->url_parameter_content_category();

        $before_timestamp = (int) $this->query('before');

        $before_date = new DateTime;

        if ($before_timestamp) {
            $before_date->setTimestamp($before_timestamp);
        }

        $items_per_page = 12;

        return $category->get_all_related_articles_before($before_date, $items_per_page);
    }
}
