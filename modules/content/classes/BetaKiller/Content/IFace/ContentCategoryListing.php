<?php
namespace BetaKiller\Content\IFace;

use BetaKiller\Helper\ContentTrait;
use BetaKiller\IFace\IFace;

class ContentCategoryListing extends IFace
{
    use ContentTrait;

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function get_data()
    {
        return [
            'categories' => $this->get_categories_data(),
        ];
    }

    protected function get_categories_data(\Model_ContentCategory $parent = null)
    {
        $data = [];

        $children = $parent ? $parent->get_children() : $this->model_factory_content_category()->get_root();

        foreach ($children as $child) {
            if (!$child->is_active()) {
                continue;
            }

            $data[] = $this->get_category_data($child);
        }

        return $data;
    }

    protected function get_category_data(\Model_ContentCategory $category = null)
    {
        return [
            'label'     =>  $category->get_label(),
            'url'       =>  $category->get_public_url(),
            'children'  =>  $this->get_categories_data($category),
        ];
    }
}
