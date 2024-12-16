<?php

declare(strict_types=1);

namespace BetaKiller\Action\Admin;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Exception\BadRequestHttpException;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Model\NotificationLogInterface;
use BetaKiller\Notification\NotificationFacade;
use BetaKiller\Url\Zone;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class RetryNotificationAction extends AbstractAction
{
    /**
     * MarkNotificationAsReadAction constructor.
     *
     * @param \BetaKiller\Notification\NotificationFacade $notification
     */
    public function __construct(private NotificationFacade $notification)
    {
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var NotificationLogInterface $logRecord */
        $logRecord = ServerRequestHelper::getEntity($request, NotificationLogInterface::class);

        if (!$logRecord) {
            throw new BadRequestHttpException('Missing notification log record');
        }

        $this->notification->retry($logRecord);

        $url = ServerRequestHelper::getUrlHelper($request)->getReadEntityUrl($logRecord, Zone::admin());

        return ResponseHelper::redirect($url);
    }
}
