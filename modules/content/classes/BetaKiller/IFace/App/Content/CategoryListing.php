<?php
namespace BetaKiller\IFace\App\Content;

use BetaKiller\Model\ContentCategory;

class CategoryListing extends AbstractAppBase
{
    /**
     * @Inject
     * @var \BetaKiller\Repository\ContentCategoryRepository
     */
    private $categoryRepository;

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function getData(): array
    {
        return [
            'categories' => $this->get_categories_data(),
        ];
    }

    protected function get_categories_data(ContentCategory $parent = null): array
    {
        $data = [];

        $children = $parent ? $parent->getChildren() : $this->categoryRepository->getRoot();

        foreach ($children as $child) {
            if (!$child->isActive()) {
                continue;
            }

            $data[] = $this->get_category_data($child);
        }

        return $data;
    }

    protected function get_category_data(\BetaKiller\Model\ContentCategory $category)
    {
        return [
            'label'     =>  $category->getLabel(),
            'url'       =>  $this->ifaceHelper->getReadEntityUrl($category),
            'children'  =>  $this->get_categories_data($category),
        ];
    }
}
