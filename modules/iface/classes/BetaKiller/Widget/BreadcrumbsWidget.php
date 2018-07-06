<?php
declare(strict_types=1);

namespace BetaKiller\Widget;

use BetaKiller\Url\UrlElementInterface;

class BreadcrumbsWidget extends AbstractWidget
{
    /**
     * @Inject
     * @var \BetaKiller\Url\UrlElementStack
     */
    private $stack;

    /**
     * @Inject
     * @var \BetaKiller\Url\UrlContainerInterface
     */
    private $urlContainer;

    /**
     * @Inject
     * @var \BetaKiller\Helper\IFaceHelper
     */
    private $ifaceHelper;

    /**
     * Returns data for View rendering
     *
     * @return array
     * @throws \BetaKiller\Url\UrlPrototypeException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getData(): array
    {
        $data = [];

        $iterator = $this->stack->getIterator();

        foreach ($iterator as $model) {
            $data[] = $this->makeBreadcrumbData($model);
        }

        return [
            'breadcrumbs' => $data,
        ];
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface $urlElement
     *
     * @return array
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    private function makeBreadcrumbData(UrlElementInterface $urlElement): array
    {
        return [
            'url'    => $this->ifaceHelper->makeUrl($urlElement, $this->urlContainer),
            'label'  => $this->ifaceHelper->getLabel($urlElement),
            'active' => $this->stack->isCurrent($urlElement),
        ];
    }
}
