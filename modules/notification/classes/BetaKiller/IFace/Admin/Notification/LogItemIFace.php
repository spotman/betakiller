<?php

declare(strict_types=1);

namespace BetaKiller\IFace\Admin\Notification;

use BetaKiller\Action\Admin\NotificationLogItemBodyAction;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\Admin\AbstractAdminIFace;
use BetaKiller\Model\NotificationLogInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class LogItemIFace extends AbstractAdminIFace
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

        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        return [
            'item' => [
                'date'         => $item->getProcessedAt()->format('d.m.Y H:i:s'),
                'status'       => $item->getStatus(),
                'is_succeeded' => $item->isSucceeded(),
                'target'       => $item->getTargetIdentity(),
                'subject'      => $item->getSubject(),
                'result'       => $item->getFailureReason(),
                'body_url'     => $urlHelper->makeCodenameUrl(NotificationLogItemBodyAction::codename()),
            ],
        ];
    }
}
