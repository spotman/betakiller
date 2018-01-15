<?php
namespace BetaKiller\IFace\App\Content;

use BetaKiller\Model\ContentCategoryInterface;

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
            'categories' => $this->getCategoriesData(),
        ];
    }

    protected function getCategoriesData(ContentCategoryInterface $parent = null): array
    {
        $data = [];

        $children = $parent ? $parent->getChildren() : $this->categoryRepository->getRoot();

        foreach ($children as $child) {
            if (!$child->isActive()) {
                continue;
            }

            $data[] = $this->getCategoryData($child);
        }

        return $data;
    }

    protected function getCategoryData(ContentCategoryInterface $category)
    {
        return [
            'label'    => $category->getLabel(),
            'url'      => $this->ifaceHelper->getReadEntityUrl($category),
            'children' => $this->getCategoriesData($category),
        ];
    }
}
