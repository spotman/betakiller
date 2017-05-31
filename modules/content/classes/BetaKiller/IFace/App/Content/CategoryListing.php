<?php
namespace BetaKiller\IFace\App\Content;

class CategoryListing extends AbstractAppBase
{
    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function getData()
    {
        return [
            'categories' => $this->get_categories_data(),
        ];
    }

    protected function get_categories_data(\Model_ContentCategory $parent = null)
    {
        $data = [];

        $children = $parent ? $parent->getChildren() : $this->model_factory_content_category()->getRoot();

        foreach ($children as $child) {
            if (!$child->is_active()) {
                continue;
            }

            $data[] = $this->get_category_data($child);
        }

        return $data;
    }

    protected function get_category_data(\Model_ContentCategory $category)
    {
        return [
            'label'     =>  $category->get_label(),
            'url'       =>  $this->ifaceHelper->getReadEntityUrl($category),
            'children'  =>  $this->get_categories_data($category),
        ];
    }
}
