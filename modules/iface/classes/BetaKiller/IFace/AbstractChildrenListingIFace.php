<?php
namespace BetaKiller\IFace;

abstract class AbstractChildrenListingIFace extends AbstractIFace
{
    /**
     * @Inject
     * @var \BetaKiller\IFace\IFaceModelTree
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

        foreach ($this->tree->getChildren($this->getModel()) as $model) {
            $data[] = [
                'label'    => $this->ifaceHelper->getLabel($model),
                'codename' => $model->getCodename(),
                'url'      => $this->ifaceHelper->makeUrl($model),
            ];
        }

        return [
            'ifaces' => $data,
        ];
    }
}
