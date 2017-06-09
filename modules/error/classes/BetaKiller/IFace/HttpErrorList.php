<?php
namespace BetaKiller\IFace;

use BetaKiller\IFace\Admin\AbstractAdminBase;

class HttpErrorList extends AbstractAdminBase
{
    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function getData(): array
    {
        $ifaces = $this->getChildren();
        $data   = [];

        foreach ($ifaces as $iface) {
            $data[] = [
                'label'    => $iface->getLabel(),
                'codename' => $iface->getCodename(),
                'url'      => $iface->url(),
            ];
        }

        return $data;
    }
}
