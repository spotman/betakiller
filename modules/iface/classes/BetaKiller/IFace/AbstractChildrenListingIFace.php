<?php
namespace BetaKiller\IFace;

use BetaKiller\Url\IFaceModelInterface;

abstract class AbstractChildrenListingIFace extends AbstractIFace
{
    /**
     * @Inject
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

    /**
     * @Inject
     * @var \BetaKiller\Helper\IFaceHelper
     */
    private $ifaceHelper;

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     * @throws \BetaKiller\Url\UrlPrototypeException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getData(): array
    {
        $data = [];

        foreach ($this->tree->getChildren($this->getModel()) as $urlElement) {
            // Show only ifaces
            if (!$urlElement instanceof IFaceModelInterface) {
                continue;
            }

            $data[] = [
                'label'    => $this->ifaceHelper->getLabel($urlElement),
                'codename' => $urlElement->getCodename(),
                'url'      => $this->ifaceHelper->makeUrl($urlElement),
            ];
        }

        return [
            'ifaces' => $data,
        ];
    }
}
