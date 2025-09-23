<?php

declare(strict_types=1);

namespace BetaKiller\Action\Admin;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Action\PostRequestActionInterface;
use BetaKiller\Helper\ActionRequestHelper;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\Admin\Notification\MessageItemIFace;
use BetaKiller\Notification\NotificationFacade;
use BetaKiller\Repository\UserRepositoryInterface;
use BetaKiller\Url\Parameter\NotificationMessageCodename;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spotman\Defence\DefinitionBuilderInterface;

readonly class NotificationMessageTestAction extends AbstractAction implements PostRequestActionInterface
{
    private const ARG_USER_ID = 'user_id';

    public function __construct(private NotificationFacade $notification, private UserRepositoryInterface $userRepo)
    {
    }

    public function definePostArguments(DefinitionBuilderInterface $builder): void
    {
        $builder
            ->string(self::ARG_USER_ID)
            ->optional();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $reqUser = ServerRequestHelper::getUser($request);

        /** @var NotificationMessageCodename $item */
        $item = ServerRequestHelper::getParameter($request, NotificationMessageCodename::class);

        $post = ActionRequestHelper::postArguments($request);

        $userId = $post->has(self::ARG_USER_ID)
            ? $post->getString(self::ARG_USER_ID)
            : null;

        $testUser = $userId ? $this->userRepo->getById($userId) : $reqUser;

        $this->notification->testDelivery($item->getValue(), $testUser);

        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        return ResponseHelper::redirect($urlHelper->makeCodenameUrl(MessageItemIFace::codename()));
    }
}
