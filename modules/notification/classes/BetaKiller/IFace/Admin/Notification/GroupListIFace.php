<?php

declare(strict_types=1);

namespace BetaKiller\IFace\Admin\Notification;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\UrlHelperInterface;
use BetaKiller\IFace\Admin\AbstractAdminIFace;
use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Repository\NotificationGroupRepositoryInterface;
use BetaKiller\Url\Zone;
use Psr\Http\Message\ServerRequestInterface;

readonly class GroupListIFace extends AbstractAdminIFace
{
    public function __construct(
        private NotificationGroupRepositoryInterface $groupRepo
    ) {
    }

    /**
     * Returns data for View
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     */
    public function getData(ServerRequestInterface $request): array
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        return [
            'enabled_groups'  => $this->getEnabledGroupsData($urlHelper),
            'disabled_groups' => $this->getDisabledGroupsData($urlHelper),
        ];
    }

    private function getEnabledGroupsData(UrlHelperInterface $urlHelper): array
    {
        $data = [];

        foreach ($this->groupRepo->getAllEnabled() as $group) {
            $data[] = $this->makeGroupData($group, $urlHelper);
        }

        return $data;
    }

    private function getDisabledGroupsData(UrlHelperInterface $urlHelper): array
    {
        $data = [];

        foreach ($this->groupRepo->getAllDisabled() as $group) {
            $data[] = $this->makeGroupData($group, $urlHelper);
        }

        return $data;
    }

    private function makeGroupData(NotificationGroupInterface $group, UrlHelperInterface $urlHelper): array
    {
        return [
            'name'       => $group->getCodename(),
            'url'        => $urlHelper->getReadEntityUrl($group, Zone::admin()),
            'roles'      => $this->getRolesData($group),
            'is_enabled' => $group->isEnabled(),
        ];
    }

    private function getRolesData(NotificationGroupInterface $group): array
    {
        $data = [];

        foreach ($group->getRoles() as $role) {
            $data[] = $role->getName();
        }

        return $data;
    }
}
