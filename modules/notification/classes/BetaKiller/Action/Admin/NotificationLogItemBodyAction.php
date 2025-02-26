<?php

declare(strict_types=1);

namespace BetaKiller\Action\Admin;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Model\NotificationLogInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class NotificationLogItemBodyAction extends AbstractAction
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var NotificationLogInterface $item */
        $item = ServerRequestHelper::getEntity($request, NotificationLogInterface::class);

        return ResponseHelper::html($item->getBody());
    }
}
