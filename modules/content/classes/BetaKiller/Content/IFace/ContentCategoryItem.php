<?php
namespace BetaKiller\Content\IFace;

use BetaKiller\Content\IFace\Base;
use Model_ContentCategory;

class ContentCategoryItem extends Base
{
    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function get_data()
    {
        $category = $this->url_parameter_content_category();

        return [
            'category'  =>  [
               'label'  =>  $category->get_label(),
            ],
            'posts'     => $this->get_category_posts($category),
        ];
    }

    protected function get_category_posts(Model_ContentCategory $category)
    {
        $data = [];

        foreach ($category->get_all_related_articles() as $article) {
            $data[] = [
                'url'   =>  $article->get_public_url(),
                'label' =>  $article->get_label(),
            ];
        }

        return $data;
    }
}
