<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Admin\Notification;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\Admin\AbstractAdminIFace;
use BetaKiller\Model\NotificationLogInterface;
use Psr\Http\Message\ServerRequestInterface;

class LogItemIFace extends AbstractAdminIFace
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
        /** @var NotificationLogInterface $item */
        $item = ServerRequestHelper::getEntity($request, NotificationLogInterface::class);

        return [
            'item' => [
                'body' => $item->getBody(),
            ],
        ];
    }
}
