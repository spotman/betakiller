<?php

declare(strict_types=1);

namespace BetaKiller\Action\Admin;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\Admin\Notification\LogIndexIFace;
use BetaKiller\Notification\NotificationFacade;
use BetaKiller\Url\Parameter\NotificationMessageCodename;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class NotificationMessageTestAction extends AbstractAction
{
    public function __construct(private NotificationFacade $notification)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = ServerRequestHelper::getUser($request);

        /** @var NotificationMessageCodename $item */
        $item = ServerRequestHelper::getParameter($request, NotificationMessageCodename::class);

        $this->notification->testDelivery($item->getValue(), $user);

        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        return ResponseHelper::redirect($urlHelper->makeCodenameUrl(LogIndexIFace::codename()));
    }
}
