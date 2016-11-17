<?php
namespace BetaKiller\Content\IFace\Article\Category;

use BetaKiller\IFace\IFace;
use Model_ContentCategory;

class Item extends IFace
{
    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function get_data()
    {
        /** @var Model_ContentCategory $category */
        $category = $this->url_parameters()->get(Model_ContentCategory::URL_PARAM);

        return [
            'category'  =>  [
               'label'  =>  $category->get_label(),
            ],
            'articles'  => $this->get_articles($category),
        ];
    }

    protected function get_articles(Model_ContentCategory $category)
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
