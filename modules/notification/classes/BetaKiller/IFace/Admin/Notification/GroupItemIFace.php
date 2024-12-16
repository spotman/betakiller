<?php

declare(strict_types=1);

namespace BetaKiller\IFace\Admin\Notification;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\Admin\AbstractAdminIFace;
use BetaKiller\Model\NotificationGroupInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class GroupItemIFace extends AbstractAdminIFace
{
    /**
     * Returns data for View
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     */
    public function getData(ServerRequestInterface $request): array
    {
        /** @var NotificationGroupInterface $group */
        $group = ServerRequestHelper::getEntity($request, NotificationGroupInterface::class);

        return [
            'group' => [
                'name'         => $group->getCodename(),
                'description'  => $group->getDescription(),
                'roles'        => $this->getRolesData($group),
                'disabled_for' => $this->getDisabledUsers($group),
            ],
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

    private function getDisabledUsers(NotificationGroupInterface $group): array
    {
        $data = [];

        foreach ($group->getDisabledUsers() as $user) {
            $data[] = [
                'name'  => $user->getFullName(),
                'email' => $user->getEmail(),
            ];
        }

        return $data;
    }
}
