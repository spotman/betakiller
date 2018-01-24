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
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getData(): array
    {
        $data = [];

        foreach ($this->tree->getChildren($this->getModel()) as $model) {
            $data[] = [
                'label'    => $model->getLabel(),
                'codename' => $model->getCodename(),
                'url'      => $this->ifaceHelper->makeUrl($model),
            ];
        }

        return [
            'ifaces' => $data,
        ];
    }
}
