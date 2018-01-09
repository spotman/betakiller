<?php
namespace BetaKiller\IFace;

abstract class AbstractChildrenListingIFace extends AbstractIFace
{
    /**
     * @Inject
     * @var \BetaKiller\IFace\IFaceProvider
     */
    private $ifaceProvider;

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

        foreach ($this->ifaceProvider->getChildren($this) as $iface) {
            $data[] = [
                'label'    => $iface->getLabel(),
                'codename' => $iface->getCodename(),
                'url'      => $iface->url(),
            ];
        }

        return [
            'ifaces' => $data,
        ];
    }
}
