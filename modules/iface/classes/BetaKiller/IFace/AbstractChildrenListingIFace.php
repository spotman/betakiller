<?php
namespace BetaKiller\IFace;

abstract class AbstractChildrenListingIFace extends AbstractIFace
{
    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function getData(): array
    {
        $data   = [];

        foreach ($this->getChildren() as $iface) {
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
