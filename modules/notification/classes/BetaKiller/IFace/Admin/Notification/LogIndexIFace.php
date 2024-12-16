<?php

declare(strict_types=1);

namespace BetaKiller\IFace\Admin\Notification;

use BetaKiller\Acl\Resource\NotificationLogResource;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\UrlHelperInterface;
use BetaKiller\IFace\Admin\AbstractAdminIFace;
use BetaKiller\Model\NotificationLogInterface;
use BetaKiller\Query\NotificationLogQuery;
use BetaKiller\Repository\NotificationLogRepositoryInterface;
use BetaKiller\Repository\UserRepositoryInterface;
use BetaKiller\Url\Parameter\Page;
use BetaKiller\Url\Zone;
use Psr\Http\Message\ServerRequestInterface;

final readonly class LogIndexIFace extends AbstractAdminIFace
{
    public const ARG_MESSAGE   = 'message';
    public const ARG_USER      = 'user';
    public const ARG_STATUS    = 'status';
    public const ARG_TRANSPORT = 'transport';

    /**
     * LogIndexIFace constructor.
     *
     * @param \BetaKiller\Repository\NotificationLogRepositoryInterface $logRepo
     * @param \BetaKiller\Repository\UserRepositoryInterface            $userRepo
     */
    public function __construct(private NotificationLogRepositoryInterface $logRepo, private UserRepositoryInterface $userRepo)
    {
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
        $urlParams = ServerRequestHelper::getUrlContainer($request);

        $itemsPerPage = 100;

        $messageCodename = $urlParams->getQueryPart(self::ARG_MESSAGE);
        $userId          = $urlParams->getQueryPart(self::ARG_USER);
        $status          = $urlParams->getQueryPart(self::ARG_STATUS);
        $transport       = $urlParams->getQueryPart(self::ARG_TRANSPORT);

        /** @var Page $pageParam */
        $pageParam   = ServerRequestHelper::getParameter($request, Page::class);
        $currentPage = $pageParam ? $pageParam->getValue() : 1;

        $user = $userId ? $this->userRepo->getById($userId) : null;

        $query = new NotificationLogQuery;

        if ($messageCodename) {
            $query->withMessageCodename($messageCodename);
        }

        if ($user) {
            $query->forUser($user);
        }

        if ($status) {
            $query->withStatus($status);
        }

        if ($transport) {
            $query->throughTransport($transport);
        }

        $items = $this->logRepo->getList($query, $currentPage, $itemsPerPage);

        $data = [];

        foreach ($items as $item) {
            $data[] = $this->getItemData($item, $urlHelper);
        }

        return [
            'items' => $data,

            'filters' => [
                'user'      => [
                    'id'    => $user ? $user->getID() : null,
                    'name'  => $user ? $user->getFullName() : null,
                    'email' => $user ? $user->getEmail() : null,
                ],
                'message'   => $messageCodename,
                'status'    => $status,
                'transport' => $transport,
            ],
        ];
    }

    private function getItemData(NotificationLogInterface $item, UrlHelperInterface $urlHelper): array
    {
        return [
            'processed_at' => $item->getProcessedAt(),
            'name'         => $item->getMessageName(),
            'transport'    => $item->getTransportName(),
            'target'       => $item->getTargetString(),
            'user_id'      => $item->getTargetUserId(),
            'is_succeeded' => $item->isSucceeded(),
            'status'       => $item->getStatus(),
            'is_read'      => $item->isRead(),
            'body_url'     => $urlHelper->getReadEntityUrl($item, Zone::admin()),
            'retry_url'    => $item->isRetryAvailable()
                ? $urlHelper->getEntityUrl($item, NotificationLogResource::ACTION_RETRY, Zone::admin())
                : null,
        ];
    }
}
