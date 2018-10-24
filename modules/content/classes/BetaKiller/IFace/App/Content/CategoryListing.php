<?php
namespace BetaKiller\IFace\App\Content;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\UrlHelper;
use BetaKiller\Model\ContentCategoryInterface;
use BetaKiller\Repository\ContentCategoryRepository;
use BetaKiller\Url\ZoneInterface;
use Psr\Http\Message\ServerRequestInterface;

final class CategoryListing extends AbstractAppBase
{
    /**
     * @var \BetaKiller\Repository\ContentCategoryRepository
     */
    private $categoryRepository;

    /**
     * CategoryListing constructor.
     *
     * @param \BetaKiller\Repository\ContentCategoryRepository $categoryRepository
     */
    public function __construct(ContentCategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getData(ServerRequestInterface $request): array
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        return [
            'categories' => $this->getCategoriesData($urlHelper),
        ];
    }

    private function getCategoriesData(UrlHelper $urlHelper, ContentCategoryInterface $parent = null): array
    {
        $data = [];

        $children = $parent ? $this->categoryRepository->getChildren($parent) : $this->categoryRepository->getRoot();

        foreach ($children as $child) {
            if (!$child->isActive()) {
                continue;
            }

            $data[] = $this->getCategoryData($child, $urlHelper);
        }

        return $data;
    }

    private function getCategoryData(ContentCategoryInterface $category, UrlHelper $urlHelper): array
    {
        return [
            'label'    => $category->getLabel(),
            'url'      => $urlHelper->getReadEntityUrl($category, ZoneInterface::PUBLIC),
            'children' => $this->getCategoriesData($urlHelper, $category),
        ];
    }
}
