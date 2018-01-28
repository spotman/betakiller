<?php
declare(strict_types=1);

namespace BetaKiller\Widget;


use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\Model\IFaceZone;

class BreadcrumbsWidget extends AbstractWidget
{
    /**
     * @Inject
     * @var \BetaKiller\IFace\IFaceModelsStack
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
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     *
     * @return array
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    private function makeBreadcrumbData(IFaceModelInterface $model): array
    {
        return [
            'url'    => $this->ifaceHelper->makeUrl($model, $this->urlContainer),
            'label'  => $this->ifaceHelper->getLabel($model),
            'active' => $this->stack->isCurrent($model),
        ];
    }
}
